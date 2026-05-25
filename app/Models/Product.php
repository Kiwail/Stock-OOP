<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public $timestamps = false;

    protected $table = 'product';

    protected $fillable = [
        'name',
        'purchase_price',
        'sale_price',
        'unit',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'deleted' => 'boolean',
        ];
    }

    public function documentLines(): HasMany
    {
        return $this->hasMany(StockDocumentProduct::class);
    }

    public function unitLabel(): string
    {
        return match ((int) $this->unit) {
            2 => 'kg',
            3 => 'l',
            default => 'gab.',
        };
    }

    public function defaultDocumentPrice(DocumentType $type): float
    {
        return match ($type) {
            DocumentType::Sale => (float) $this->sale_price,
            default => (float) $this->purchase_price,
        };
    }
}
