<?php

namespace App\Models\Order;

use App\Models\Address\UserAddress;
use App\Models\Geo\DeliveryZone;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDelivery extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'order_id';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'address_id',
        'delivery_zone_id',
        'total_delivery_fee',
    ];

    protected function casts(): array
    {
        return [
            'total_delivery_fee' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }
}
