<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Support\FirmaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Stock::query()
            ->where('firma_id', FirmaContext::firmaId())
            ->where('deleted', false)
            ->orderBy('name')
            ->get();

        return view('warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('warehouses.form', ['warehouse' => new Stock]);
    }

    public function store(Request $request): RedirectResponse
    {
        Stock::query()->create([
            ...$this->validated($request),
            'firma_id' => FirmaContext::firmaId(),
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Noliktava izveidota.');
    }

    public function edit(Stock $warehouse): View
    {
        $this->authorizeWarehouse($warehouse);

        return view('warehouses.form', ['warehouse' => $warehouse]);
    }

    public function update(Request $request, Stock $warehouse): RedirectResponse
    {
        $this->authorizeWarehouse($warehouse);
        $warehouse->update($this->validated($request));

        return redirect()->route('warehouses.index')->with('success', 'Noliktava saglabāta.');
    }

    public function destroy(Stock $warehouse): RedirectResponse
    {
        $this->authorizeWarehouse($warehouse);
        $warehouse->update(['deleted' => true]);

        return redirect()->route('warehouses.index')->with('success', 'Noliktava noņemta.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
    }

    private function authorizeWarehouse(Stock $warehouse): void
    {
        abort_unless(
            (int) $warehouse->firma_id === (int) FirmaContext::firmaId() && ! $warehouse->deleted,
            404
        );
    }
}
