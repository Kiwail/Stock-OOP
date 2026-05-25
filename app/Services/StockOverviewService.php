<?php

namespace App\Services;

use App\Models\Firma;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Stock;
use App\Models\StockDocument;
use Illuminate\Support\Collection;

class StockOverviewService
{
    public function showcaseFirma(): ?Firma
    {
        return Firma::query()->where('deleted', false)->orderBy('id')->first();
    }

    public function statsForFirma(?int $firmaId): array
    {
        $stockQty = 0.0;

        if ($firmaId) {
            $stockQty = (float) ProductStock::query()
                ->where('firma_id', $firmaId)
                ->where('cnt', '>', 0)
                ->sum('cnt');
        }

        return [
            'stock_qty' => $stockQty,
            'products' => Product::query()->where('deleted', false)->count(),
            'open_documents' => $firmaId
                ? StockDocument::query()
                    ->where('firma_id', $firmaId)
                    ->where('posted', false)
                    ->where('cancelled', false)
                    ->where('deleted', false)
                    ->count()
                : 0,
            'warehouses' => $firmaId
                ? Stock::query()
                    ->where('firma_id', $firmaId)
                    ->where('deleted', false)
                    ->count()
                : 0,
        ];
    }

    public function mainStock(?int $firmaId): ?Stock
    {
        if (! $firmaId) {
            return null;
        }

        return Stock::query()
            ->where('firma_id', $firmaId)
            ->where('deleted', false)
            ->orderBy('id')
            ->first();
    }

    public function topBalances(?int $firmaId, ?int $stockId, int $limit = 5): Collection
    {
        if (! $firmaId || ! $stockId) {
            return collect();
        }

        return ProductStock::query()
            ->with(['product', 'incomeDocument'])
            ->where('firma_id', $firmaId)
            ->where('stock_id', $stockId)
            ->where('cnt', '>', 0)
            ->orderByDesc('date_upd')
            ->limit($limit)
            ->get();
    }

    public function recentDocuments(?int $firmaId, int $limit = 3): Collection
    {
        if (! $firmaId) {
            return collect();
        }

        return StockDocument::query()
            ->with(['sourceStock', 'destinationStock', 'operator'])
            ->where('firma_id', $firmaId)
            ->where('deleted', false)
            ->orderByDesc('date_add')
            ->limit($limit)
            ->get();
    }
}
