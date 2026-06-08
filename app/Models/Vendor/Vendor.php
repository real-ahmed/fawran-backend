<?php

namespace App\Models\Vendor;

use App\Builders\VendorBuilder;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Models\Catalog\VendorBrandSubmission;
use App\Models\Catalog\VendorCategorySubmission;
use App\Models\Geo\VendorDeliveryZone;
use App\Models\Inventory\Supplier;
use App\Models\Model;
use App\Models\Order\SubOrder;
use App\Models\Platform\OrderCommission;
use App\Models\Product\VendorItem;
use App\Models\User;
use App\Traits\Scopes\AdminZoneScope;
use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vendor extends Model
{
    use AdminZoneScope, \App\Traits\HasImages, HasFactory;

    public $timestamps = true;

    protected static function newFactory(): VendorFactory
    {
        return VendorFactory::new();
    }

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('deliveryZones', fn ($query) => $query->whereIn('delivery_zone_id', $zoneIds));
    }

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
        'status' => VendorStatus::OFFLINE->value,
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'type' => VendorType::class,
            'status' => VendorStatus::class,
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
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

    public function categorySubmissions(): HasMany
    {
        return $this->hasMany(VendorCategorySubmission::class);
    }

    public function brandSubmissions(): HasMany
    {
        return $this->hasMany(VendorBrandSubmission::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(VendorSubscription::class)->where('status', 'active');
    }

    public function newEloquentBuilder($query): VendorBuilder
    {
        return new VendorBuilder($query);
    }
}
