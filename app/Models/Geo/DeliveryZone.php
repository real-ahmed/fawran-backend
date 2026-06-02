<?php

namespace App\Models\Geo;

use App\Builders\DeliveryZoneBuilder;
use App\Models\Admin;
use App\Models\Courier\Courier;
use App\Models\Vendor\VendorDeliveryZone;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    use AdminZoneScope;

    public $timestamps = false;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereIn('delivery_zones.id', $zoneIds);
    }

    protected $fillable = [
        'name',
        'polygon',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function vehicleFees(): HasMany
    {
        return $this->hasMany(DeliveryZoneVehicleFee::class);
    }

    public function storeDeliveryZones(): HasMany
    {
        return $this->hasMany(VendorDeliveryZone::class);
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_delivery_zones');
    }

    public function couriers(): HasMany
    {
        return $this->hasMany(Courier::class);
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  Builder  $query
     */
    public function newEloquentBuilder($query): DeliveryZoneBuilder
    {
        return new DeliveryZoneBuilder($query);
    }
}
