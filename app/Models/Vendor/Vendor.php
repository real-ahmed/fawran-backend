<?php

namespace App\Models\Vendor;

use App\Models\Geo\VendorDeliveryZone;
use App\Models\Inventory\Supplier;
use App\Models\Media\Media;
use App\Models\Order\SubOrder;
use App\Models\Platform\OrderCommission;
use App\Models\Product\VendorItem;
use App\Enums\VendorType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Vendor extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'owner_id',
        'name',
        'type',
        'email',
        'phone',
        'latitude',
        'longitude',
        'formatted_address',
        'is_active',
        'status',
    ];

    protected $attributes = [
        'is_active' => true,
        'status' => \App\Enums\VendorStatus::OFFLINE->value,
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'type' => VendorType::class,
            'status' => \App\Enums\VendorStatus::class,
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function description(): HasOne
    {
        return $this->hasOne(VendorDescription::class);
    }

    public function customCommission(): HasOne
    {
        return $this->hasOne(VendorCustomCommission::class);
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(VendorWorkingHour::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(VendorStaff::class);
    }

    public function storeItems(): HasMany
    {
        return $this->hasMany(VendorItem::class);
    }

    public function deliveryZones(): HasMany
    {
        return $this->hasMany(VendorDeliveryZone::class);
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(OrderCommission::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }
}
