<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockDocumentLedger;
use App\Services\CsvExportService;
use App\Support\FirmaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MovementController extends Controller
{
    public function __construct(private CsvExportService $csv)
    {
    }

    public function index(Request $request): View
    {
        $firmaId = FirmaContext::firmaId();
        $movements = $this->filteredMovements($request, $firmaId)->get();
        $products = Product::query()->where('deleted', false)->orderBy('name')->get();
        $warehouses = $this->warehouses($firmaId);

        return view('movements.index', compact('movements', 'products', 'warehouses'));
    }

    public function export(Request $request): StreamedResponse
    {
        $movements = $this->filteredMovements($request, FirmaContext::firmaId())->get();

        return $this->csv->download('stock-movements.csv', [
            'Document',
            'Type',
            'Date',
            'Product',
            'Warehouse',
            'Zone',
            'Income batch',
            'Quantity delta',
        ], $movements->map(fn (StockDocumentLedger $movement) => [
            $movement->document_id,
            $movement->document?->typeEnum()->label(),
            $movement->document?->date_add?->format('Y-m-d H:i:s'),
            $movement->product?->name,
            $movement->stock?->name,
            $movement->zone,
            $movement->income_id,
            (float) $movement->cnt_delta,
        ]));
    }

    private function filteredMovements(Request $request, int $firmaId): \Illuminate\Database\Eloquent\Builder
    {
        return StockDocumentLedger::query()
            ->with(['document', 'product', 'stock', 'incomeDocument'])
            ->where('firma_id', $firmaId)
            ->when($request->integer('product_id'), fn ($query, int $id) => $query->where('product_id', $id))
            ->when($request->integer('stock_id'), fn ($query, int $id) => $query->where('stock_id', $id))
            ->when($request->filled('zone'), fn ($query) => $query->where('zone', $request->string('zone')->toString()))
            ->when($request->integer('income_id'), fn ($query, int $id) => $query->where('income_id', $id))
            ->when($request->integer('document_id'), fn ($query, int $id) => $query->where('document_id', $id))
            ->orderByDesc('id');
    }

    private function warehouses(int $firmaId): Collection
    {
        return Stock::query()
            ->where('firma_id', $firmaId)
            ->where('deleted', false)
            ->orderBy('name')
            ->get();
    }
}
