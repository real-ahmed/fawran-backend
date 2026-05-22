<?php

namespace App\Models\Courier;

use App\Enums\VehicleType;
use App\Models\Geo\DeliveryZone;
use App\Models\Order\Delivery;
use App\Models\P2p\P2pAssignment;
use App\Models\Payment\CourierCashCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Courier extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'delivery_zone_id',
        'vehicle_type',
        'plate_number',
        'is_online',
    ];

    protected $attributes = [
        'is_online' => false,
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'is_online' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(CourierLocation::class);
    }

    public function document(): HasOne
    {
        return $this->hasOne(CourierDocument::class);
    }

    public function approval(): HasOne
    {
        return $this->hasOne(CourierApproval::class);
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function p2pAssignments(): HasMany
    {
        return $this->hasMany(P2pAssignment::class);
    }

    public function cashCollections(): HasMany
    {
        return $this->hasMany(CourierCashCollection::class);
    }
}
