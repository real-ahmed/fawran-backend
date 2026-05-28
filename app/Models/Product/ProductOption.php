<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_item_id',
        'name',
        'is_required',
        'max_selections',
    ];

    protected $attributes = [
        'is_required' => false,
        'max_selections' => 1,
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_required' => 'boolean',
            'max_selections' => 'integer',
        ];
    }

    public function storeItem(): BelongsTo
    {
        return $this->belongsTo(VendorItem::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class);
    }
}
