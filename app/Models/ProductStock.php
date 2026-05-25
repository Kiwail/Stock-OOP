<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'product_stock';

    protected $fillable = [
        'product_id',
        'stock_id',
        'firma_id',
        'income_id',
        'zone',
        'cnt',
        'price',
        'date_upd',
    ];

    public function zoneLabel(): string
    {
        $zone = $this->zone ?? '—';

        return $zone === '—' ? '—' : 'Zona '.$zone;
    }

    protected function casts(): array
    {
        return [
            'cnt' => 'decimal:3',
            'price' => 'decimal:2',
            'date_upd' => 'datetime',
        ];
    }

    /** @return array<string, int|string> */
    public function batchKey(): array
    {
        return [
            'product_id' => $this->product_id,
            'stock_id' => $this->stock_id,
            'firma_id' => $this->firma_id,
            'income_id' => $this->income_id,
            'zone' => $this->normalizeZoneValue($this->zone),
        ];
    }

    public static function normalizeZoneValue(?string $zone): string
    {
        $zone = strtoupper(trim((string) $zone));

        return $zone !== '' ? $zone : '—';
    }

    public function scopeForBatch(Builder $query, array $key): Builder
    {
        return $query->where([
            'product_id' => $key['product_id'],
            'stock_id' => $key['stock_id'],
            'firma_id' => $key['firma_id'],
            'income_id' => $key['income_id'],
            'zone' => self::normalizeZoneValue($key['zone'] ?? null),
        ]);
    }

    protected function setKeysForSaveQuery($query)
    {
        return $this->scopeForBatch($query, $this->batchKey());
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function incomeDocument(): BelongsTo
    {
        return $this->belongsTo(StockDocument::class, 'income_id');
    }
}
