<?php

namespace App\Models\Courier;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierLocation extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'courier_id';

    public $incrementing = false;

    protected $fillable = [
        'courier_id',
        'latitude',
        'longitude',
        'located_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'located_at' => 'datetime',
        ];
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }
}
