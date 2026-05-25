<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockDocumentLedger extends Model
{
    public $timestamps = false;

    protected $table = 'stock_document_ledger';

    protected $fillable = [
        'document_id',
        'product_id',
        'stock_id',
        'firma_id',
        'income_id',
        'zone',
        'cnt_delta',
    ];

    protected function casts(): array
    {
        return [
            'cnt_delta' => 'decimal:3',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(StockDocument::class);
    }
}
