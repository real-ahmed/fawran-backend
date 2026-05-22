<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCustomCommission extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'store_id';

    public $incrementing = false;

    protected $fillable = [
        'store_id',
        'commission_percentage',
    ];

    protected function casts(): array
    {
        return [
            'commission_percentage' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
