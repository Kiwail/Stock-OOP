<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_document', function (Blueprint $table) {
            $table->boolean('cancelled')->default(false)->after('posted');
        });

        Schema::table('stock_document_product', function (Blueprint $table) {
            $table->string('zone', 16)->nullable()->after('price');
        });

        Schema::create('stock_document_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')
                ->constrained('stock_document')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('product_id')
                ->constrained('product')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('stock_id')
                ->constrained('stock')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('firma_id')
                ->constrained('firma')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('income_id')
                ->constrained('stock_document')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('zone', 16)->default('—');
            $table->decimal('cnt_delta', 12, 3);
        });

        Schema::rename('product_stock', 'product_stock_old');

        Schema::create('product_stock', function (Blueprint $table) {
            $table->foreignId('product_id');
            $table->foreignId('stock_id');
            $table->foreignId('firma_id');
            $table->foreignId('income_id');
            $table->string('zone', 16)->default('—');
            $table->decimal('cnt', 12, 3)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->dateTime('date_upd')->useCurrent();

            $table->primary(['product_id', 'stock_id', 'firma_id', 'income_id', 'zone']);
            $table->foreign('product_id', 'product_stock_zone_product_fk')->references('id')->on('product')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('stock_id', 'product_stock_zone_stock_fk')->references('id')->on('stock')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('firma_id', 'product_stock_zone_firma_fk')->references('id')->on('firma')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('income_id', 'product_stock_zone_income_fk')->references('id')->on('stock_document')->restrictOnDelete()->cascadeOnUpdate();
        });

        if (Schema::hasTable('product_stock_old')) {
            $rows = DB::table('product_stock_old')->get();
            foreach ($rows as $row) {
                DB::table('product_stock')->insert([
                    'product_id' => $row->product_id,
                    'stock_id' => $row->stock_id,
                    'firma_id' => $row->firma_id,
                    'income_id' => $row->income_id,
                    'zone' => '—',
                    'cnt' => $row->cnt,
                    'price' => $row->price,
                    'date_upd' => $row->date_upd,
                ]);
            }
            Schema::drop('product_stock_old');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_document_ledger');

        Schema::table('stock_document_product', function (Blueprint $table) {
            $table->dropColumn('zone');
        });

        Schema::table('stock_document', function (Blueprint $table) {
            $table->dropColumn('cancelled');
        });

        Schema::rename('product_stock', 'product_stock_new');
        Schema::create('product_stock', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('product')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('stock_id')->constrained('stock')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('firma_id')->constrained('firma')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('income_id')->constrained('stock_document')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('cnt', 12, 3)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->dateTime('date_upd')->useCurrent();
            $table->primary(['product_id', 'stock_id', 'firma_id', 'income_id']);
        });
        Schema::dropIfExists('product_stock_new');
    }
};
