<?php

namespace App\Models\Catalog;

use App\Builders\CategoryBuilder;
use App\Models\Product\MasterProduct;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
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

    public function hierarchy(): HasOne
    {
        return $this->hasOne(CategoryHierarchy::class, 'child_category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_category_id')
            ->via('hierarchy');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CategoryHierarchy::class, 'parent_category_id');
    }

    public function icon(): HasOne
    {
        return $this->hasOne(CategoryIcon::class);
    }

    public function masterProducts(): HasMany
    {
        return $this->hasMany(MasterProduct::class);
    }

    public function vendorSubmission(): HasOne
    {
        return $this->hasOne(VendorCategorySubmission::class, 'category_id');
    }

    public function newEloquentBuilder($query): CategoryBuilder
    {
        return new CategoryBuilder($query);
    }
}
