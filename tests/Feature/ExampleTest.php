<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Models\Firma;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_user_without_firma_is_not_redirected_back_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/');
    }

    public function test_document_validation_sums_duplicate_product_lines(): void
    {
        $firma = Firma::query()->where('name', 'SIA Demo Noliktava')->firstOrFail();
        $admin = User::query()->where('email', 'admin@instock.lv')->firstOrFail();
        $stock = Stock::query()->where('firma_id', $firma->id)->orderBy('id')->firstOrFail();
        $batch = ProductStock::query()->where('firma_id', $firma->id)->where('stock_id', $stock->id)->firstOrFail();
        $available = (int) floor((float) $batch->cnt);

        $this->actingAs($admin)
            ->withSession(['firma_id' => $firma->id])
            ->post('/documents', [
                'type' => DocumentType::Writeoff->value,
                'source_stock_id' => $stock->id,
                'lines' => [
                    ['product_id' => $batch->product_id, 'cnt' => $available],
                    ['product_id' => $batch->product_id, 'cnt' => 1],
                ],
            ])
            ->assertSessionHasErrors('lines.0.cnt');
    }

    public function test_deleted_products_cannot_be_used_in_new_documents(): void
    {
        $firma = Firma::query()->where('name', 'SIA Demo Noliktava')->firstOrFail();
        $admin = User::query()->where('email', 'admin@instock.lv')->firstOrFail();
        $stock = Stock::query()->where('firma_id', $firma->id)->orderBy('id')->firstOrFail();
        $product = Product::query()->firstOrFail();
        $product->update(['deleted' => true]);

        $this->actingAs($admin)
            ->withSession(['firma_id' => $firma->id])
            ->post('/documents', [
                'type' => DocumentType::Income->value,
                'destination_stock_id' => $stock->id,
                'lines' => [
                    ['product_id' => $product->id, 'cnt' => 1, 'zone' => 'A-12'],
                ],
            ])
            ->assertSessionHasErrors('lines.0.product_id');
    }
}
