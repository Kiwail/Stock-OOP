<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CsvExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function __construct(private CsvExportService $csv)
    {
    }

    public function index(): View
    {
        $products = Product::query()
            ->where('deleted', false)
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products'));
    }

    public function export(): StreamedResponse
    {
        $products = Product::query()
            ->where('deleted', false)
            ->orderBy('name')
            ->get();

        return $this->csv->download('products.csv', [
            'ID',
            'Name',
            'Purchase price',
            'Sale price',
            'Unit',
        ], $products->map(fn (Product $product) => [
            $product->id,
            $product->name,
            (float) $product->purchase_price,
            (float) $product->sale_price,
            $product->unitLabel(),
        ]));
    }

    public function create(): View
    {
        return view('products.form', ['product' => new Product(['unit' => 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validated($request);
        Product::query()->create($attributes);

        return redirect()->route('products.index')->with('success', 'Produkts pievienots.');
    }

    public function edit(Product $product): View
    {
        abort_if($product->deleted, 404);

        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->deleted, 404);

        $product->update($this->validated($request));

        return redirect()->route('products.index')->with('success', 'Produkts saglabāts.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['deleted' => true]);

        return redirect()->route('products.index')->with('success', 'Produkts noņemts no saraksta.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'integer', 'in:1,2,3'],
        ]);
    }
}
