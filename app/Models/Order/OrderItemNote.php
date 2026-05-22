<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemNote extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'order_item_id';

    public $incrementing = false;

    protected $fillable = [
        'order_item_id',
        'notes',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
