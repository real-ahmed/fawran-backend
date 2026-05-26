<?php

namespace App\Models\Order;

use App\Enums\SubOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubOrder extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'sub_total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sub_total' => 'decimal:2',
            'status' => SubOrderStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
