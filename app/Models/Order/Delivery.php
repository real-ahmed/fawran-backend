<?php

namespace App\Models\Order;

use App\Enums\DeliveryStatus;
use App\Models\Courier\Courier;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Delivery extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'courier_id',
        'fee_share',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'fee_share' => 'decimal:2',
            'status' => DeliveryStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function pickup(): HasOne
    {
        return $this->hasOne(DeliveryPickup::class);
    }

    public function dropoff(): HasOne
    {
        return $this->hasOne(DeliveryDropoff::class);
    }
}
