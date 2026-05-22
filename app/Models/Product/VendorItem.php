<?php

namespace App\Models\Product;

use App\Models\Media\SavedItem;
use App\Models\Order\OrderItem;
use App\Models\Vendor\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VendorItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'master_product_id',
        'price',
        'is_available',
    ];

    protected $attributes = [
        'is_available' => true,
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function masterProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(VendorItemInventory::class);
    }

    public function restaurantDishDetail(): HasOne
    {
        return $this->hasOne(RestaurantDishDetail::class);
    }

    public function productOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function savedItems(): HasMany
    {
        return $this->hasMany(SavedItem::class);
    }
}
