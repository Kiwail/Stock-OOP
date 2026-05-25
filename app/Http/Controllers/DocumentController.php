<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Stock;
use App\Models\StockDocument;
use App\Models\StockDocumentProduct;
use App\Services\StockDocumentService;
use App\Support\FirmaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class DocumentController extends Controller
{
    public function __construct(private StockDocumentService $documents)
    {
    }

    public function index(): View
    {
        $documents = StockDocument::query()
            ->with(['sourceStock', 'destinationStock', 'operator', 'lines.product'])
            ->where('firma_id', FirmaContext::firmaId())
            ->where('deleted', false)
            ->orderByDesc('date_add')
            ->get();

        return view('documents.index', compact('documents'));
    }

    public function cancel(StockDocument $document): RedirectResponse
    {
        $this->authorizeDocument($document);
        abort_unless(FirmaContext::isAdmin(), 403);

        try {
            $this->documents->cancel($document);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Dokuments atcelts. Atlikumi atjaunināti.');
    }

    public function create(): View
    {
        return $this->formView(new StockDocument([
            'type' => DocumentType::Income->value,
            'date_add' => now(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $document = $this->persist($request, new StockDocument);

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Dokuments saglabāts kā melnraksts.');
    }

    public function show(StockDocument $document): View
    {
        $this->authorizeDocument($document);
        $document->load(['lines.product', 'sourceStock', 'destinationStock', 'operator']);

        return view('documents.show', compact('document'));
    }

    public function post(StockDocument $document): RedirectResponse
    {
        $this->authorizeDocument($document);

        try {
            $this->documents->post($document);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Dokuments apstiprināts. Atlikumi atjaunināti.');
    }

    private function persist(Request $request, StockDocument $document): StockDocument
    {
        $data = $request->validate([
            'type' => ['required', 'integer', 'in:1,2,3,4'],
            'comment' => ['nullable', 'string', 'max:500'],
            'source_stock_id' => ['nullable', 'exists:stock,id'],
            'destination_stock_id' => ['nullable', 'exists:stock,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'exists:product,id'],
            'lines.*.cnt' => ['required', 'integer', 'min:1'],
            'lines.*.price' => ['nullable', 'numeric', 'min:0'],
            'lines.*.zone' => ['nullable', 'string', 'max:16', 'regex:/^[A-Za-z]-\d{2}$/'],
        ]);

        $type = DocumentType::from((int) $data['type']);

        if ($type === DocumentType::Income) {
            foreach ($data['lines'] as $index => $line) {
                if (empty($line['zone'])) {
                    throw ValidationException::withMessages([
                        "lines.{$index}.zone" => 'Saņemšanai jānorāda glabāšanas zona (piem., A-12).',
                    ]);
                }
            }
        }

        $firmaId = FirmaContext::firmaId();

        $this->validateStocksForType($type, $data, $firmaId);
        $this->validateLinesAgainstStock($type, $data, $firmaId);

        $document->fill([
            'type' => $type->value,
            'comment' => $data['comment'] ?? null,
            'source_stock_id' => $data['source_stock_id'] ?? null,
            'destination_stock_id' => $data['destination_stock_id'] ?? null,
            'operator_id' => Auth::id(),
            'firma_id' => $firmaId,
            'date_add' => now(),
            'posted' => false,
            'deleted' => false,
        ]);
        $document->save();

        $document->lines()->delete();

        $products = Product::query()
            ->whereIn('id', collect($data['lines'])->pluck('product_id'))
            ->get()
            ->keyBy('id');

        foreach ($data['lines'] as $line) {
            $product = $products->get($line['product_id']);
            $price = (float) ($line['price'] ?? 0);

            if ($price <= 0 && $product) {
                $price = $product->defaultDocumentPrice($type);
            }

            StockDocumentProduct::query()->create([
                'document_id' => $document->id,
                'product_id' => $line['product_id'],
                'cnt' => (int) $line['cnt'],
                'price' => $price,
                'zone' => isset($line['zone']) ? strtoupper($line['zone']) : null,
            ]);
        }

        return $document;
    }

    private function validateLinesAgainstStock(DocumentType $type, array $data, int $firmaId): void
    {
        if ($type === DocumentType::Income) {
            return;
        }

        $stockId = (int) $data['source_stock_id'];
        $available = $this->stockProductMap($firmaId)->get($stockId, collect())->keyBy('id');

        foreach ($data['lines'] as $index => $line) {
            $productId = (int) $line['product_id'];
            $row = $available->get($productId);

            if (! $row) {
                throw ValidationException::withMessages([
                    "lines.{$index}.product_id" => 'Šī prece nav pieejama izvēlētajā noliktavā.',
                ]);
            }

            if ((int) $line['cnt'] > (int) $row['qty']) {
                throw ValidationException::withMessages([
                    "lines.{$index}.cnt" => "Maksimāli pieejams: {$row['qty']} {$row['unit']}.",
                ]);
            }
        }
    }

    private function validateStocksForType(DocumentType $type, array $data, int $firmaId): void
    {
        $stockIds = collect([$data['source_stock_id'] ?? null, $data['destination_stock_id'] ?? null])
            ->filter()
            ->unique();

        $validCount = Stock::query()
            ->where('firma_id', $firmaId)
            ->where('deleted', false)
            ->whereIn('id', $stockIds)
            ->count();

        abort_unless($validCount === $stockIds->count(), 403);

        match ($type) {
            DocumentType::Income => abort_unless($data['destination_stock_id'], 422),
            DocumentType::Writeoff, DocumentType::Sale => abort_unless($data['source_stock_id'], 422),
            DocumentType::Transfer => abort_unless(
                $data['source_stock_id'] && $data['destination_stock_id']
                && $data['source_stock_id'] !== $data['destination_stock_id'],
                422
            ),
        };
    }

    private function formView(StockDocument $document): View
    {
        $products = Product::query()->where('deleted', false)->orderBy('name')->get();
        $warehouses = Stock::query()
            ->where('firma_id', FirmaContext::firmaId())
            ->where('deleted', false)
            ->orderBy('name')
            ->get();

        $catalogProducts = $products->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'unit' => $p->unitLabel(),
            'purchase' => (float) $p->purchase_price,
            'sale' => (float) $p->sale_price,
        ])->values();

        $stockProducts = $this->stockProductMap(FirmaContext::firmaId())
            ->map(fn (Collection $rows) => $rows->values())
            ->all();

        return view('documents.create', compact(
            'document',
            'products',
            'warehouses',
            'catalogProducts',
            'stockProducts',
        ));
    }

    private function stockProductMap(int $firmaId): Collection
    {
        return ProductStock::query()
            ->with('product')
            ->where('firma_id', $firmaId)
            ->where('cnt', '>=', 1)
            ->get()
            ->groupBy('stock_id')
            ->map(function (Collection $rows) {
                return $rows
                    ->groupBy('product_id')
                    ->map(function (Collection $batches) {
                        $product = $batches->first()->product;
                        $qty = (int) floor($batches->sum(fn ($b) => (float) $b->cnt));

                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'unit' => $product->unitLabel(),
                            'qty' => $qty,
                            'price' => (float) $batches->first()->price,
                        ];
                    })
                    ->values();
            });
    }

    private function authorizeDocument(StockDocument $document): void
    {
        abort_unless(
            (int) $document->firma_id === (int) FirmaContext::firmaId() && ! $document->deleted,
            404
        );
    }
}
