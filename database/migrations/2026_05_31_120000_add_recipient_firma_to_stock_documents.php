<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_document', function (Blueprint $table) {
            $table->foreignId('recipient_firma_id')
                ->nullable()
                ->after('firma_id')
                ->constrained('firma')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('stock_document', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipient_firma_id');
        });
    }
};
