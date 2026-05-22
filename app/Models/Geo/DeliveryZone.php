<?php

namespace App\Models\Geo;

use App\Models\Admin;
use App\Models\Courier\Courier;
use App\Models\Store\StoreDeliveryZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    public $timestamps = false;

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
        return $this->hasMany(StoreDeliveryZone::class);
    }

    public function admins(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_delivery_zones');
    }

    public function couriers(): HasMany
    {
        return $this->hasMany(Courier::class);
    }
}
