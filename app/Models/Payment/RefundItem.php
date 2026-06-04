<?php

namespace App\Models\Payment;

use App\Models\Model;
use App\Models\Order\OrderItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'refund_request_id',
        'order_item_id',
        'quantity_returned',
        'refund_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity_returned' => 'decimal:3',
            'refund_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function refundRequest(): BelongsTo
    {
        return $this->belongsTo(RefundRequest::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
