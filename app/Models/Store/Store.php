<?php

namespace App\Models\Store;

use App\Models\Geo\StoreDeliveryZone;
use App\Models\Inventory\Supplier;
use App\Models\Media\Media;
use App\Models\Order\SubOrder;
use App\Models\Platform\OrderCommission;
use App\Models\Product\StoreItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Store extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'average_rating',
        'total_reviews',
        'is_active',
        'is_open',
    ];

    protected $attributes = [
        'average_rating' => 0.00,
        'total_reviews' => 0,
        'is_active' => true,
        'is_open' => false,
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'average_rating' => 'decimal:2',
            'is_active' => 'boolean',
            'is_open' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function description(): HasOne
    {
        return $this->hasOne(StoreDescription::class);
    }

    public function customCommission(): HasOne
    {
        return $this->hasOne(StoreCustomCommission::class);
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(StoreWorkingHour::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(StoreStaff::class);
    }

    public function storeItems(): HasMany
    {
        return $this->hasMany(StoreItem::class);
    }

    public function deliveryZones(): HasMany
    {
        return $this->hasMany(StoreDeliveryZone::class);
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
