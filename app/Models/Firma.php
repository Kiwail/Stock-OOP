<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Firma extends Model
{
    public $timestamps = false;

    protected $table = 'firma';

    protected $fillable = [
        'name',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'deleted' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function stockDocuments(): HasMany
    {
        return $this->hasMany(StockDocument::class);
    }
}
