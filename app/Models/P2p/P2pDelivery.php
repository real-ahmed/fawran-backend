<?php

namespace App\Models\P2p;

use App\Enums\P2pDeliveryStatus;
use App\Models\Geo\DeliveryZone;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class P2pDelivery extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sender_id',
        'delivery_zone_id',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_address',
        'dropoff_latitude',
        'dropoff_longitude',
        'dropoff_address',
        'recipient_name',
        'recipient_phone',
        'distance_km',
        'delivery_fee',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'pickup_latitude' => 'decimal:8',
            'pickup_longitude' => 'decimal:8',
            'dropoff_latitude' => 'decimal:8',
            'dropoff_longitude' => 'decimal:8',
            'distance_km' => 'decimal:3',
            'delivery_fee' => 'decimal:2',
            'status' => P2pDeliveryStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function description(): HasOne
    {
        return $this->hasOne(P2pDescription::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(P2pAssignment::class);
    }

    public function pickup(): HasOne
    {
        return $this->hasOne(P2pPickup::class);
    }

    public function dropoff(): HasOne
    {
        return $this->hasOne(P2pDropoff::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(P2pPayment::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(P2pStatusLog::class);
    }
}
