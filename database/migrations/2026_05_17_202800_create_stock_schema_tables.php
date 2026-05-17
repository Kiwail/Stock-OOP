<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firma', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('deleted')->default(false);
        });

        Schema::create('firma_user', function (Blueprint $table) {
            $table->foreignId('firma_id')
                ->constrained('firma')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('role')->default('operator');
            $table->timestamps();

            $table->primary(['firma_id', 'user_id']);
        });

        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('firma_id')
                ->constrained('firma')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->boolean('deleted')->default(false);
        });

        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->integer('unit');
            $table->boolean('deleted')->default(false);
        });

        Schema::create('stock_document', function (Blueprint $table) {
            $table->id();
            $table->integer('type');
            $table->dateTime('date_add')->useCurrent();
            $table->foreignId('operator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('source_stock_id')
                ->nullable()
                ->constrained('stock')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('destination_stock_id')
                ->nullable()
                ->constrained('stock')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('firma_id')
                ->constrained('firma')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->boolean('posted')->default(false);
            $table->boolean('deleted')->default(false);
            $table->string('comment')->nullable();
        });

        Schema::create('product_stock', function (Blueprint $table) {
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
            $table->decimal('cnt', 12, 3)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->dateTime('date_upd')->useCurrent();

            $table->primary(['product_id', 'stock_id', 'firma_id', 'income_id']);
        });

        Schema::create('stock_document_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')
                ->constrained('stock_document')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('product_id')
                ->constrained('product')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('cnt', 12, 3)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->foreignId('income_id')
                ->nullable()
                ->constrained('stock_document')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_document_product');
        Schema::dropIfExists('product_stock');
        Schema::dropIfExists('stock_document');
        Schema::dropIfExists('product');
        Schema::dropIfExists('stock');
        Schema::dropIfExists('firma_user');
        Schema::dropIfExists('firma');
    }
};
