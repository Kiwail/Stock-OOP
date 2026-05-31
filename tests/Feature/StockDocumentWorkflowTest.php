<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Models\Firma;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Stock;
use App\Models\StockDocument;
use App\Models\StockDocumentLedger;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockDocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_income_document_posting_adds_stock_and_ledger_entries(): void
    {
        $firma = $this->demoFirma();
        $admin = $this->demoAdmin();
        $stock = $this->mainStock();
        $product = $this->product('UTP Cat.6 kabelis');

        $this->actingAs($admin)->withSession(['firma_id' => $firma->id]);

        $this->post('/documents', [
            'type' => DocumentType::Income->value,
            'destination_stock_id' => $stock->id,
            'comment' => 'Test income',
            'lines' => [
                ['product_id' => $product->id, 'cnt' => 5, 'price' => 2.75, 'zone' => 'D-01'],
            ],
        ])->assertRedirect();

        $document = StockDocument::query()->where('comment', 'Test income')->firstOrFail();

        $this->post("/documents/{$document->id}/post")->assertRedirect("/documents/{$document->id}");

        $this->assertTrue($document->fresh()->posted);
        $this->assertSame(5.0, $this->stockQuantity($product, $stock, $firma, $document->id, 'D-01'));
        $this->assertDatabaseHas('stock_document_ledger', [
            'document_id' => $document->id,
            'product_id' => $product->id,
            'stock_id' => $stock->id,
            'firma_id' => $firma->id,
            'income_id' => $document->id,
            'zone' => 'D-01',
            'cnt_delta' => 5,
        ]);
    }

    public function test_writeoff_document_posting_decreases_stock_and_cancellation_restores_it(): void
    {
        $firma = $this->demoFirma();
        $admin = $this->demoAdmin();
        $stock = $this->mainStock();
        $product = $this->product('UTP Cat.6 kabelis');
        $before = $this->stockQuantity($product, $stock, $firma);

        $this->actingAs($admin)->withSession(['firma_id' => $firma->id]);

        $this->post('/documents', [
            'type' => DocumentType::Writeoff->value,
            'source_stock_id' => $stock->id,
            'comment' => 'Test writeoff',
            'lines' => [
                ['product_id' => $product->id, 'cnt' => 7],
            ],
        ])->assertRedirect();

        $document = StockDocument::query()->where('comment', 'Test writeoff')->firstOrFail();

        $this->post("/documents/{$document->id}/post")->assertRedirect("/documents/{$document->id}");

        $this->assertSame($before - 7.0, $this->stockQuantity($product, $stock, $firma));
        $this->assertTrue($document->fresh()->posted);

        $this->post("/documents/{$document->id}/cancel")->assertRedirect("/documents/{$document->id}");

        $this->assertSame($before, $this->stockQuantity($product, $stock, $firma));
        $this->assertTrue($document->fresh()->cancelled);
    }

    public function test_transfer_document_moves_stock_between_warehouses(): void
    {
        $firma = $this->demoFirma();
        $admin = $this->demoAdmin();
        $source = $this->mainStock();
        $destination = Stock::query()->where('firma_id', $firma->id)->where('name', 'Noliktava A')->firstOrFail();
        $product = $this->product('UTP Cat.6 kabelis');
        $beforeSource = $this->stockQuantity($product, $source, $firma);
        $beforeDestination = $this->stockQuantity($product, $destination, $firma);

        $this->actingAs($admin)->withSession(['firma_id' => $firma->id]);

        $this->post('/documents', [
            'type' => DocumentType::Transfer->value,
            'source_stock_id' => $source->id,
            'destination_stock_id' => $destination->id,
            'comment' => 'Test transfer',
            'lines' => [
                ['product_id' => $product->id, 'cnt' => 4],
            ],
        ])->assertRedirect();

        $document = StockDocument::query()->where('comment', 'Test transfer')->firstOrFail();

        $this->post("/documents/{$document->id}/post")->assertRedirect("/documents/{$document->id}");

        $this->assertSame($beforeSource - 4.0, $this->stockQuantity($product, $source, $firma));
        $this->assertSame($beforeDestination + 4.0, $this->stockQuantity($product, $destination, $firma));
        $this->assertSame(2, StockDocumentLedger::query()->where('document_id', $document->id)->count());
    }

    public function test_sale_document_decreases_stock(): void
    {
        $firma = $this->demoFirma();
        $admin = $this->demoAdmin();
        $stock = $this->mainStock();
        $product = $this->product('Svītrkodu skeneris');
        $before = $this->stockQuantity($product, $stock, $firma);

        $this->actingAs($admin)->withSession(['firma_id' => $firma->id]);

        $this->post('/documents', [
            'type' => DocumentType::Sale->value,
            'source_stock_id' => $stock->id,
            'recipient_firma_id' => $firma->id,
            'comment' => 'Test sale',
            'lines' => [
                ['product_id' => $product->id, 'cnt' => 3],
            ],
        ])->assertRedirect();

        $document = StockDocument::query()->where('comment', 'Test sale')->firstOrFail();

        $this->post("/documents/{$document->id}/post")->assertRedirect("/documents/{$document->id}");

        $this->assertSame($before - 3.0, $this->stockQuantity($product, $stock, $firma));
        $this->assertSame($firma->id, $document->fresh()->recipient_firma_id);
    }

    public function test_operator_cannot_access_admin_product_or_warehouse_actions(): void
    {
        $firma = $this->demoFirma();
        $operator = User::query()->where('email', 'operators@instock.lv')->firstOrFail();

        $this->actingAs($operator)->withSession(['firma_id' => $firma->id]);

        $this->get('/products/create')->assertForbidden();
        $this->post('/products', [
            'name' => 'Restricted product',
            'purchase_price' => 1,
            'sale_price' => 2,
            'unit' => 1,
        ])->assertForbidden();
        $this->get('/warehouses/create')->assertForbidden();
        $this->post('/warehouses', ['name' => 'Restricted warehouse'])->assertForbidden();
    }

    private function demoFirma(): Firma
    {
        return Firma::query()->where('name', 'SIA Demo Noliktava')->firstOrFail();
    }

    private function demoAdmin(): User
    {
        return User::query()->where('email', 'admin@instock.lv')->firstOrFail();
    }

    private function mainStock(): Stock
    {
        return Stock::query()
            ->where('firma_id', $this->demoFirma()->id)
            ->where('name', 'Galvenā noliktava')
            ->firstOrFail();
    }

    private function product(string $name): Product
    {
        return Product::query()->where('name', $name)->firstOrFail();
    }

    private function stockQuantity(Product $product, Stock $stock, Firma $firma, ?int $incomeId = null, ?string $zone = null): float
    {
        return (float) ProductStock::query()
            ->where('product_id', $product->id)
            ->where('stock_id', $stock->id)
            ->where('firma_id', $firma->id)
            ->when($incomeId, fn ($query) => $query->where('income_id', $incomeId))
            ->when($zone, fn ($query) => $query->where('zone', $zone))
            ->sum('cnt');
    }
}
