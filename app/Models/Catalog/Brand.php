<?php

namespace App\Models\Catalog;

use App\Builders\BrandBuilder;
use App\Models\Product\RetailProductDetail;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Brand extends Model
{
    use HasImages;

    public $timestamps = false;

    protected $fillable = [
        'name',
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

    public function retailProductDetails(): HasMany
    {
        return $this->hasMany(RetailProductDetail::class);
    }

    public function vendorSubmission(): HasOne
    {
        return $this->hasOne(VendorBrandSubmission::class, 'brand_id');
    }

    public function newEloquentBuilder($query): BrandBuilder
    {
        return new BrandBuilder($query);
    }
}
