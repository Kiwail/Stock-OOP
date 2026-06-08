<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Stock;
use App\Services\CsvExportService;
use App\Support\FirmaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BalanceController extends Controller
{
    public function __construct(private CsvExportService $csv)
    {
    }

    public function index(Request $request): View
    {
        $firmaId = $this->visibleFirmaId();
        $warehouses = $this->warehouses($firmaId);
        $products = Product::query()->where('deleted', false)->orderBy('name')->get();
        $zones = $this->zones($firmaId);

        $stockId = $request->filled('stock_id') ? $request->integer('stock_id') : null;
        $balances = $this->filteredBalances($request, $firmaId, $stockId)->get();

        return view('balances.index', compact('warehouses', 'products', 'zones', 'balances', 'stockId'));
    }

    public function export(Request $request): StreamedResponse
    {
        $firmaId = $this->visibleFirmaId();
        $stockId = $request->filled('stock_id') ? $request->integer('stock_id') : null;
        $balances = $this->filteredBalances($request, $firmaId, $stockId)->get();

        return $this->csv->download('balances.csv', [
            'Product',
            'Warehouse',
            'Zone',
            'Income batch',
            'Quantity',
            'Unit',
            'Price',
            'Value',
        ], $balances->map(fn (ProductStock $balance) => [
            $balance->product->name,
            $balance->stock->name,
            $balance->zone,
            $balance->income_id,
            (float) $balance->cnt,
            $balance->product->unitLabel(),
            (float) $balance->price,
            (float) $balance->cnt * (float) $balance->price,
        ]));
    }

    private function filteredBalances(Request $request, ?int $firmaId, ?int $stockId): \Illuminate\Database\Eloquent\Builder
    {
        return ProductStock::query()
            ->with(['product', 'stock', 'incomeDocument'])
            ->whereHas('product', fn ($query) => $query->where('deleted', false))
            ->when($firmaId, fn ($query, int $id) => $query->where('firma_id', $id))
            ->where('cnt', '>', 0)
            ->when($stockId, fn ($q) => $q->where('stock_id', $stockId))
            ->when($request->integer('product_id'), fn ($query, int $id) => $query->where('product_id', $id))
            ->when($request->filled('zone'), fn ($query) => $query->where('zone', $request->string('zone')->toString()))
            ->when($request->integer('income_id'), fn ($query, int $id) => $query->where('income_id', $id))
            ->when($request->boolean('low_stock'), fn ($query) => $query->where('cnt', '<', 10))
            ->orderBy('product_id')
            ->orderBy('zone');
    }

    private function warehouses(?int $firmaId): Collection
    {
        return Stock::query()
            ->when($firmaId, fn ($query, int $id) => $query->where('firma_id', $id))
            ->where('deleted', false)
            ->orderBy('name')
            ->get();
    }

    private function zones(?int $firmaId): Collection
    {
        return ProductStock::query()
            ->when($firmaId, fn ($query, int $id) => $query->where('firma_id', $id))
            ->where('cnt', '>', 0)
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone');
    }

    private function visibleFirmaId(): ?int
    {
        return FirmaContext::isAdmin() ? null : FirmaContext::firmaId();
    }
}
