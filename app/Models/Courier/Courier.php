<?php

namespace App\Models\Courier;

use App\Builders\CourierBuilder;
use App\Enums\VehicleType;
use App\Models\Order\Delivery;
use App\Models\P2p\P2pAssignment;
use App\Models\Payment\CourierCashCollection;
use App\Models\Payment\Wallet;
use App\Models\User;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Courier extends Model
{
    use AdminZoneScope;

    public $timestamps = false;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('location', function ($q) use ($zoneIds) {
            $q->whereExists(function ($subQuery) use ($zoneIds) {
                $subQuery->select(DB::raw(1))
                    ->from('delivery_zones')
                    ->whereIn('id', $zoneIds)
                    ->whereRaw("ST_Contains(delivery_zones.polygon, ST_GeomFromText(CONCAT('POINT(', courier_locations.longitude, ' ', courier_locations.latitude, ')')))");
            });
        });
    }

    protected $fillable = [
        'user_id',
        'national_id',
        'vehicle_type',
        'plate_number',
        'is_online',
        'cod_blocked',
        'rejected_at',
    ];

    protected $attributes = [
        'is_online' => false,
        'cod_blocked' => false,
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'is_online' => 'boolean',
            'cod_blocked' => 'boolean',
            'rejected_at' => 'datetime',
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

    /**
     * Get the wallet through the user relationship (convenience accessor).
     */
    public function getUserWalletAttribute(): ?Wallet
    {
        return $this->user?->wallet;
    }

    public function newEloquentBuilder($query): CourierBuilder
    {
        return new CourierBuilder($query);
    }
}
