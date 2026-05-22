<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryPickup extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'delivery_id';

    public $incrementing = false;

    protected $fillable = [
        'delivery_id',
        'picked_up_at',
    ];

    protected function casts(): array
    {
        return [
            'picked_up_at' => 'datetime',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
