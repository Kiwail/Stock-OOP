<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockDocumentProduct extends Model
{
    public $timestamps = false;

    protected $table = 'stock_document_product';

    protected $fillable = [
        'document_id',
        'product_id',
        'cnt',
        'price',
        'zone',
        'income_id',
    ];

    protected function casts(): array
    {
        return [
            'cnt' => 'decimal:3',
            'price' => 'decimal:2',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(StockDocument::class, 'document_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
