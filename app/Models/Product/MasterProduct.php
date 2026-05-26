<?php

namespace App\Models\Product;

use App\Enums\UnitType;
use App\Models\Catalog\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MasterProduct extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'name',
        'unit_type',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'unit_type' => UnitType::class,
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function description(): HasOne
    {
        return $this->hasOne(MasterProductDescription::class);
    }

    public function retailDetail(): HasOne
    {
        return $this->hasOne(RetailProductDetail::class);
    }

    public function storeItems(): HasMany
    {
        return $this->hasMany(VendorItem::class);
    }

    public function vendorSubmission(): HasOne
    {
        return $this->hasOne(VendorMasterProductSubmission::class, 'master_product_id');
    }
}
