<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\Stock;
use App\Support\FirmaContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BalanceController extends Controller
{
    public function index(Request $request): View
    {
        $firmaId = FirmaContext::firmaId();

        $warehouses = Stock::query()
            ->where('firma_id', $firmaId)
            ->where('deleted', false)
            ->orderBy('name')
            ->get();

        $stockId = $request->integer('stock_id') ?: $warehouses->first()?->id;

        $balances = ProductStock::query()
            ->with(['product', 'stock', 'incomeDocument'])
            ->where('firma_id', $firmaId)
            ->where('cnt', '>', 0)
            ->when($stockId, fn ($q) => $q->where('stock_id', $stockId))
            ->orderBy('product_id')
            ->get();

        return view('balances.index', compact('warehouses', 'balances', 'stockId'));
    }
}
