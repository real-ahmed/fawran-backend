<?php

namespace App\Models\Catalog;

use App\Models\Product\RetailProductDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
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

    public function vendorSubmission(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(VendorBrandSubmission::class, 'brand_id');
    }
}
