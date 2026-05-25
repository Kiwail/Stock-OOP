<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\ProductStock;
use App\Models\StockDocument;
use App\Models\StockDocumentLedger;
use App\Models\StockDocumentProduct;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class StockDocumentService
{
    public function post(StockDocument $document): void
    {
        if ($document->posted) {
            throw new RuntimeException('Dokuments jau ir apstiprināts.');
        }

        if ($document->cancelled) {
            throw new RuntimeException('Atceltu dokumentu nevar apstiprināt.');
        }

        $document->load('lines.product');

        $type = DocumentType::from($document->type);

        DB::transaction(function () use ($document, $type): void {
            match ($type) {
                DocumentType::Income => $this->postIncome($document),
                DocumentType::Writeoff => $this->postWriteoff($document),
                DocumentType::Transfer => $this->postTransfer($document),
                DocumentType::Sale => $this->postSale($document),
            };

            $document->update(['posted' => true]);
        });
    }

    public function cancel(StockDocument $document): void
    {
        if (! $document->posted) {
            throw new RuntimeException('Melnrakstu nevar atcelt — dzēsiet vai labojiet dokumentu.');
        }

        if ($document->cancelled) {
            throw new RuntimeException('Dokuments jau ir atcelts.');
        }

        DB::transaction(function () use ($document): void {
            $entries = StockDocumentLedger::query()
                ->where('document_id', $document->id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get();

            foreach ($entries as $entry) {
                $this->applyDelta(
                    $entry->product_id,
                    $entry->stock_id,
                    $entry->firma_id,
                    $entry->income_id,
                    $this->normalizeZone($entry->zone),
                    (float) -$entry->cnt_delta,
                    null,
                );
            }

            $document->update(['cancelled' => true]);
        });
    }

    public function postIncome(StockDocument $document): void
    {
        if (! $document->destination_stock_id) {
            throw new InvalidArgumentException('Saņemšanai nepieciešama mērķa noliktava.');
        }

        foreach ($document->lines as $line) {
            $zone = $this->normalizeZone($line->zone);
            $qty = (float) $line->cnt;

            $this->applyDelta(
                $line->product_id,
                $document->destination_stock_id,
                $document->firma_id,
                $document->id,
                $zone,
                $qty,
                (float) $line->price,
            );

            $this->recordLedger($document, $line->product_id, $document->destination_stock_id, $document->id, $zone, $qty);
        }
    }

    public function postWriteoff(StockDocument $document): void
    {
        $this->deductFromStock($document, $document->source_stock_id);
    }

    public function postSale(StockDocument $document): void
    {
        $this->deductFromStock($document, $document->source_stock_id);
    }

    public function postTransfer(StockDocument $document): void
    {
        if ($document->source_stock_id === $document->destination_stock_id) {
            throw new InvalidArgumentException('Avota un mērķa noliktava nedrīkst būt vienāda.');
        }

        foreach ($document->lines as $line) {
            $remaining = (float) $line->cnt;
            $batches = ProductStock::query()
                ->where('product_id', $line->product_id)
                ->where('stock_id', $document->source_stock_id)
                ->where('firma_id', $document->firma_id)
                ->where('cnt', '>', 0)
                ->orderBy('date_upd')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min((float) $batch->cnt, $remaining);
                $zone = $this->normalizeZone($batch->zone);

                $this->applyDelta(
                    $line->product_id,
                    $document->source_stock_id,
                    $document->firma_id,
                    $batch->income_id,
                    $zone,
                    -$take,
                    null,
                );

                $this->recordLedger($document, $line->product_id, $document->source_stock_id, $batch->income_id, $zone, -$take);

                $this->applyDelta(
                    $line->product_id,
                    $document->destination_stock_id,
                    $document->firma_id,
                    $batch->income_id,
                    $zone,
                    $take,
                    (float) $batch->price,
                );

                $this->recordLedger($document, $line->product_id, $document->destination_stock_id, $batch->income_id, $zone, $take);

                $remaining -= $take;
            }

            if ($remaining > 0.0001) {
                throw new RuntimeException('Nepietiekams atlikums pārvietošanai: '.$line->product->name);
            }
        }
    }

    private function deductFromStock(StockDocument $document, ?int $stockId): void
    {
        if (! $stockId) {
            throw new InvalidArgumentException('Nepieciešama avota noliktava.');
        }

        foreach ($document->lines as $line) {
            $remaining = (float) $line->cnt;
            $batches = ProductStock::query()
                ->where('product_id', $line->product_id)
                ->where('stock_id', $stockId)
                ->where('firma_id', $document->firma_id)
                ->where('cnt', '>', 0)
                ->orderBy('date_upd')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min((float) $batch->cnt, $remaining);
                $zone = $this->normalizeZone($batch->zone);

                $this->applyDelta(
                    $line->product_id,
                    $stockId,
                    $document->firma_id,
                    $batch->income_id,
                    $zone,
                    -$take,
                    null,
                );

                $this->recordLedger($document, $line->product_id, $stockId, $batch->income_id, $zone, -$take);

                $remaining -= $take;
            }

            if ($remaining > 0.0001) {
                throw new RuntimeException('Nepietiekams atlikums: '.$line->product->name);
            }
        }
    }

    private function applyDelta(
        int $productId,
        int $stockId,
        int $firmaId,
        int $incomeId,
        string $zone,
        float $delta,
        ?float $price,
    ): void {
        $zone = ProductStock::normalizeZoneValue($zone);
        $key = [
            'product_id' => $productId,
            'stock_id' => $stockId,
            'firma_id' => $firmaId,
            'income_id' => $incomeId,
            'zone' => $zone,
        ];

        $batch = ProductStock::query()->forBatch($key)->lockForUpdate()->first();

        if (! $batch) {
            if ($delta < 0) {
                throw new RuntimeException('Nepietiekams atlikums zonā '.$zone.'.');
            }

            ProductStock::query()->create([
                ...$key,
                'cnt' => $delta,
                'price' => $price ?? 0,
                'date_upd' => now(),
            ]);

            return;
        }

        $newCnt = (float) $batch->cnt + $delta;

        if ($newCnt < -0.0001) {
            throw new RuntimeException('Nepietiekams atlikums zonā '.$zone.'.');
        }

        ProductStock::query()->forBatch($key)->update([
            'cnt' => max(0, $newCnt),
            'price' => $price ?? $batch->price,
            'date_upd' => now(),
        ]);
    }

    private function recordLedger(
        StockDocument $document,
        int $productId,
        int $stockId,
        int $incomeId,
        string $zone,
        float $cntDelta,
    ): void {
        StockDocumentLedger::query()->create([
            'document_id' => $document->id,
            'product_id' => $productId,
            'stock_id' => $stockId,
            'firma_id' => $document->firma_id,
            'income_id' => $incomeId,
            'zone' => $zone,
            'cnt_delta' => $cntDelta,
        ]);
    }

    private function normalizeZone(?string $zone): string
    {
        return ProductStock::normalizeZoneValue($zone);
    }
}
