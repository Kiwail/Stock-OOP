<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\UserRole;
use App\Models\Firma;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockDocument;
use App\Models\StockDocumentProduct;
use App\Models\User;
use App\Services\StockDocumentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StockDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Firma::query()->where('name', 'SIA Demo Noliktava')->exists()
            || User::query()->whereIn('email', ['admin@instock.lv', 'operators@instock.lv'])->exists()) {
            return;
        }

        $firma = Firma::query()->create(['name' => 'SIA Demo Noliktava']);

        $admin = User::query()->create([
            'name' => 'Administrators',
            'email' => 'admin@instock.lv',
            'password' => Hash::make('password'),
        ]);

        $operator = User::query()->create([
            'name' => 'Operators',
            'email' => 'operators@instock.lv',
            'password' => Hash::make('password'),
        ]);

        $firma->users()->attach($admin->id, ['role' => UserRole::Admin->value]);
        $firma->users()->attach($operator->id, ['role' => UserRole::Operator->value]);

        $mainStock = Stock::query()->create([
            'name' => 'Galvenā noliktava',
            'firma_id' => $firma->id,
            'deleted' => false,
        ]);
        $mainStockId = $mainStock->id;

        Stock::query()->insert([
            ['name' => 'Veikala noliktava', 'firma_id' => $firma->id, 'deleted' => false],
            ['name' => 'Noliktava A', 'firma_id' => $firma->id, 'deleted' => false],
            ['name' => 'Noliktava B', 'firma_id' => $firma->id, 'deleted' => false],
        ]);

        $products = [
            Product::query()->create([
                'name' => 'UTP Cat.6 kabelis',
                'purchase_price' => 1.20,
                'sale_price' => 2.40,
                'unit' => 1,
                'deleted' => false,
            ]),
            Product::query()->create([
                'name' => 'Svītrkodu skeneris',
                'purchase_price' => 85.00,
                'sale_price' => 129.00,
                'unit' => 1,
                'deleted' => false,
            ]),
            Product::query()->create([
                'name' => 'Iepakošanas lente',
                'purchase_price' => 0.80,
                'sale_price' => 1.50,
                'unit' => 1,
                'deleted' => false,
            ]),
        ];

        $income = StockDocument::query()->create([
            'type' => DocumentType::Income->value,
            'date_add' => now(),
            'operator_id' => $admin->id,
            'destination_stock_id' => $mainStockId,
            'firma_id' => $firma->id,
            'posted' => false,
            'cancelled' => false,
            'deleted' => false,
            'comment' => 'Demo saņemšana',
        ]);

        $lines = [
            ['product' => $products[0], 'cnt' => 240, 'price' => 1.20, 'zone' => 'A-12'],
            ['product' => $products[1], 'cnt' => 15, 'price' => 85.00, 'zone' => 'B-04'],
            ['product' => $products[2], 'cnt' => 6, 'price' => 0.80, 'zone' => 'C-02'],
        ];

        foreach ($lines as $line) {
            StockDocumentProduct::query()->create([
                'document_id' => $income->id,
                'product_id' => $line['product']->id,
                'cnt' => $line['cnt'],
                'price' => $line['price'],
                'zone' => $line['zone'],
            ]);
        }

        app(StockDocumentService::class)->post($income);

        StockDocument::query()->create([
            'type' => DocumentType::Transfer->value,
            'date_add' => now()->subHour(),
            'operator_id' => $operator->id,
            'source_stock_id' => $mainStockId,
            'destination_stock_id' => Stock::query()->where('name', 'Noliktava A')->value('id'),
            'firma_id' => $firma->id,
            'posted' => false,
            'cancelled' => false,
            'deleted' => false,
            'comment' => 'Plānota pārvietošana',
        ]);
    }
}
