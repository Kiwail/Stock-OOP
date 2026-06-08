<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Firma;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Stock;
use App\Models\StockDocument;
use App\Models\StockDocumentProduct;
use App\Models\User;
use App\Services\CsvExportService;
use App\Services\StockDocumentService;
use App\Support\FirmaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        private StockDocumentService $documents,
        private CsvExportService $csv,
    )
    {
    }

    public function index(Request $request): View
    {
        $firmaId = $this->visibleFirmaId();
        $visibleTypes = [
            DocumentType::Income->value,
            DocumentType::Writeoff->value,
            DocumentType::Transfer->value,
        ];
        $type = $request->integer('type') ?: DocumentType::Income->value;
        $type = in_array($type, $visibleTypes, true) ? $type : DocumentType::Income->value;

        $request->merge([
            'type' => $type,
        ]);

        $createDocument = new StockDocument([
            'type' => $type,
            'date_add' => now(),
        ]);
        $documents = $this->filteredDocuments($request, $firmaId)->get();
        $filterWarehouses = $this->warehouses($firmaId);
        $operators = $this->operators($firmaId);
        $formData = $this->formData($createDocument);

        return view('documents.index', array_merge(
            compact('documents', 'filterWarehouses', 'operators', 'createDocument'),
            [
                'catalogProducts' => $formData['catalogProducts'],
                'stockProducts' => $formData['stockProducts'],
                'warehouses' => $formData['warehouses'],
                'lineRows' => $formData['lineRows'],
                'currentOperator' => $formData['currentOperator'],
                'recipientFirms' => $formData['recipientFirms'],
            ],
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $documents = $this->filteredDocuments($request, $this->visibleFirmaId())->get();

        return $this->csv->download('documents.csv', [
            'ID',
            'Type',
            'Date',
            'Source warehouse',
            'Destination warehouse',
            'Recipient firma',
            'Operator',
            'Status',
            'Comment',
        ], $documents->map(fn (StockDocument $document) => [
            $document->id,
            $document->typeEnum()->label(),
            $document->date_add?->format('Y-m-d H:i:s'),
            $document->sourceStock?->name,
            $document->destinationStock?->name,
            $document->recipientFirma?->name,
            $document->operator?->name,
            $this->statusLabel($document),
            $document->comment,
        ]));
    }

    public function cancel(StockDocument $document): RedirectResponse
    {
        $this->authorizeDocument($document);
        $this->authorizeCurrentFirmaDocument($document);
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

    public function edit(StockDocument $document): View
    {
        $this->authorizeDraft($document);

        return $this->formView($document);
    }

    public function store(Request $request): RedirectResponse
    {
        $document = $this->persist($request, new StockDocument);

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Dokuments saglabāts kā melnraksts.');
    }

    public function update(Request $request, StockDocument $document): RedirectResponse
    {
        $this->authorizeDraft($document);

        $document = $this->persist($request, $document);

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Melnraksts saglabāts.');
    }

    public function destroy(StockDocument $document): RedirectResponse
    {
        $this->authorizeDraft($document);
        $document->update(['deleted' => true]);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Melnraksts dzēsts.');
    }

    public function show(StockDocument $document): View
    {
        $this->authorizeDocument($document);
        $document->load(['lines.product', 'sourceStock', 'destinationStock', 'operator', 'recipientFirma']);

        return view('documents.show', compact('document'));
    }

    public function print(StockDocument $document): View
    {
        $this->authorizeDocument($document);
        $document->load(['lines.product', 'sourceStock', 'destinationStock', 'operator', 'recipientFirma']);

        return view('documents.print', compact('document'));
    }

    public function post(StockDocument $document): RedirectResponse
    {
        $this->authorizeDocument($document);
        $this->authorizeCurrentFirmaDocument($document);

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
            'recipient_firma_id' => ['nullable', Rule::exists('firma', 'id')->where(fn ($query) => $query->where('deleted', false))],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => [
                'required',
                Rule::exists('product', 'id')->where(fn ($query) => $query->where('deleted', false)),
            ],
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
            'recipient_firma_id' => $type === DocumentType::Sale ? $data['recipient_firma_id'] ?? null : null,
            'operator_id' => Auth::id(),
            'firma_id' => $firmaId,
            'date_add' => $document->exists ? $document->date_add : now(),
            'posted' => false,
            'deleted' => false,
        ]);
        $document->save();

        $document->lines()->delete();

        $products = Product::query()
            ->whereIn('id', collect($data['lines'])->pluck('product_id'))
            ->where('deleted', false)
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

        $requested = collect($data['lines'])
            ->groupBy(fn (array $line) => (int) $line['product_id'])
            ->map(fn (Collection $lines) => [
                'qty' => $lines->sum(fn (array $line) => (int) $line['cnt']),
                'index' => $lines->keys()->first(),
            ]);

        foreach ($requested as $productId => $request) {
            $row = $available->get($productId);

            if (! $row) {
                throw ValidationException::withMessages([
                    "lines.{$request['index']}.product_id" => 'Šī prece nav pieejama izvēlētajā noliktavā.',
                ]);
            }

            if ((int) $request['qty'] > (int) $row['qty']) {
                throw ValidationException::withMessages([
                    "lines.{$request['index']}.cnt" => "Maksimāli pieejams: {$row['qty']} {$row['unit']}.",
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

        if (
            $type === DocumentType::Transfer
            && $data['source_stock_id']
            && $data['destination_stock_id']
            && (int) $data['source_stock_id'] === (int) $data['destination_stock_id']
        ) {
            throw ValidationException::withMessages([
                'destination_stock_id' => 'Avota un merka noliktavai jabut atskirigai.',
            ]);
        }

        match ($type) {
            DocumentType::Income => abort_unless($data['destination_stock_id'], 422),
            DocumentType::Writeoff => abort_unless($data['source_stock_id'], 422),
            DocumentType::Sale => abort_unless($data['source_stock_id'] && $data['recipient_firma_id'], 422),
            DocumentType::Transfer => abort_unless(
                $data['source_stock_id'] && $data['destination_stock_id']
                && (int) $data['source_stock_id'] !== (int) $data['destination_stock_id'],
                422
            ),
        };
    }

    private function formView(StockDocument $document): View
    {
        return view('documents.create', $this->formData($document));
    }

    private function formData(StockDocument $document): array
    {
        $document->loadMissing('lines');

        $currentOperator = Auth::user();
        $products = Product::query()->where('deleted', false)->orderBy('name')->get();
        $warehouses = $this->warehouses(FirmaContext::firmaId());
        $recipientFirms = Firma::query()
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

        $lineRows = old('lines');
        if (! is_array($lineRows)) {
            $lineRows = $document->lines->map(fn (StockDocumentProduct $line) => [
                'product_id' => $line->product_id,
                'zone' => $line->zone,
                'cnt' => (float) $line->cnt,
                'price' => (float) $line->price,
            ])->values()->all();
        }

        if ($lineRows === []) {
            $lineRows = [['product_id' => '', 'zone' => '', 'cnt' => 1, 'price' => '']];
        }

        return compact(
            'document',
            'products',
            'warehouses',
            'recipientFirms',
            'catalogProducts',
            'stockProducts',
            'lineRows',
            'currentOperator',
        );
    }

    private function stockProductMap(int $firmaId): Collection
    {
        return ProductStock::query()
            ->with('product')
            ->whereHas('product', fn ($query) => $query->where('deleted', false))
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
            (FirmaContext::isAdmin() || (int) $document->firma_id === (int) FirmaContext::firmaId()) && ! $document->deleted,
            404
        );
    }

    private function authorizeDraft(StockDocument $document): void
    {
        $this->authorizeDocument($document);
        $this->authorizeCurrentFirmaDocument($document);

        abort_if($document->posted || $document->cancelled, 403);
    }

    private function authorizeCurrentFirmaDocument(StockDocument $document): void
    {
        abort_unless((int) $document->firma_id === (int) FirmaContext::firmaId(), 403);
    }

    private function filteredDocuments(Request $request, ?int $firmaId): \Illuminate\Database\Eloquent\Builder
    {
        return StockDocument::query()
            ->with(['sourceStock', 'destinationStock', 'operator', 'recipientFirma', 'lines.product'])
            ->when($firmaId, fn ($query, int $id) => $query->where('firma_id', $id))
            ->where('deleted', false)
            ->when($request->integer('type'), fn ($query, int $type) => $query->where('type', $type))
            ->when($request->filled('status'), function ($query) use ($request): void {
                match ($request->string('status')->toString()) {
                    'draft' => $query->where('posted', false)->where('cancelled', false),
                    'posted' => $query->where('posted', true)->where('cancelled', false),
                    'cancelled' => $query->where('cancelled', true),
                    default => null,
                };
            })
            ->when($request->integer('source_stock_id'), fn ($query, int $id) => $query->where('source_stock_id', $id))
            ->when($request->integer('destination_stock_id'), fn ($query, int $id) => $query->where('destination_stock_id', $id))
            ->when($request->integer('operator_id'), fn ($query, int $id) => $query->where('operator_id', $id))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('date_add', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('date_add', '<=', $request->input('date_to')))
            ->when($request->filled('q'), fn ($query) => $query->where('comment', 'like', '%'.$request->string('q')->toString().'%'))
            ->orderByDesc('date_add');
    }

    private function warehouses(?int $firmaId): Collection
    {
        return Stock::query()
            ->when($firmaId, fn ($query, int $id) => $query->where('firma_id', $id))
            ->where('deleted', false)
            ->orderBy('name')
            ->get();
    }

    private function operators(?int $firmaId): Collection
    {
        return User::query()
            ->when($firmaId, fn ($query, int $id) => $query->whereHas('firmas', fn ($firmas) => $firmas->where('firma.id', $id)))
            ->orderBy('name')
            ->get();
    }

    private function visibleFirmaId(): ?int
    {
        return FirmaContext::isAdmin() ? null : FirmaContext::firmaId();
    }

    private function statusLabel(StockDocument $document): string
    {
        if ($document->cancelled) {
            return 'Atcelts';
        }

        return $document->posted ? 'Apstiprināts' : 'Melnraksts';
    }
}
