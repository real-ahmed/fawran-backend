<?php

namespace App\Models\Order;

use App\Models\Product\VendorItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sub_order_id',
        'vendor_item_id',
        'quantity',
        'unit_price',
        'options_price',
    ];

    protected $attributes = [
        'options_price' => 0.00,
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'options_price' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function vendorItem(): BelongsTo
    {
        return $this->belongsTo(VendorItem::class);
    }

    public function note(): HasOne
    {
        return $this->hasOne(OrderItemNote::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(OrderItemOption::class);
    }
}
