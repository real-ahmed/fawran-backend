<?php

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorDeliveryZone extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'delivery_zone_id',
        'min_order_amount',
        'estimated_delivery_time',
    ];

    protected function casts(): array
    {
        return [
            'min_order_amount' => 'decimal:2',
            'estimated_delivery_time' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }
}
