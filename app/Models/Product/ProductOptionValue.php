<?php

namespace App\Models\Product;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionValue extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_option_id',
        'name',
        'additional_price',
        'is_available',
    ];

    protected $attributes = [
        'additional_price' => 0.00,
        'is_available' => true,
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'additional_price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }
}
