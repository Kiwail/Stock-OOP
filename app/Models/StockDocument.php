<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'recipient_firma_id',
        'posted',
        'cancelled',
        'deleted',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'date_add' => 'datetime',
            'posted' => 'boolean',
            'cancelled' => 'boolean',
            'deleted' => 'boolean',
        ];
    }

    public function firma(): BelongsTo
    {
        return $this->belongsTo(Firma::class);
    }

    public function recipientFirma(): BelongsTo
    {
        return $this->belongsTo(Firma::class, 'recipient_firma_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function sourceStock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'source_stock_id');
    }

    public function destinationStock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'destination_stock_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StockDocumentProduct::class, 'document_id');
    }

    public function typeEnum(): DocumentType
    {
        return DocumentType::from($this->type);
    }
}
