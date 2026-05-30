<?php

namespace App\Models\Geo;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryZoneVehicleFee extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'delivery_zone_id',
        'vehicle_type',
        'base_delivery_fee',
        'fee_per_km',
        'intra_zone_flat_fee',
        'max_delivery_fee',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'base_delivery_fee' => 'decimal:2',
            'fee_per_km' => 'decimal:2',
            'intra_zone_flat_fee' => 'decimal:2',
            'max_delivery_fee' => 'decimal:2',
        ];
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }
}
