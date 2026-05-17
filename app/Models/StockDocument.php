<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockDocument extends Model
{
    public $timestamps = false;

    protected $table = 'stock_document';

    protected $fillable = [
        'type',
        'date_add',
        'operator_id',
        'source_stock_id',
        'destination_stock_id',
        'firma_id',
        'posted',
        'deleted',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'date_add' => 'datetime',
            'posted' => 'boolean',
            'deleted' => 'boolean',
        ];
    }

    public function firma(): BelongsTo
    {
        return $this->belongsTo(Firma::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
