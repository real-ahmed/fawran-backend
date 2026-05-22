<?php

namespace App\Models\Order;

use App\Models\Product\ProductOption;
use App\Models\Product\ProductOptionValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemOption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_item_id',
        'product_option_id',
        'product_option_value_id',
        'additional_price',
    ];

    protected function casts(): array
    {
        return [
            'additional_price' => 'decimal:2',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }

    public function productOptionValue(): BelongsTo
    {
        return $this->belongsTo(ProductOptionValue::class);
    }
}
