<?php

namespace App\Models\P2p;

use App\Enums\P2pPaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class P2pPayment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'p2p_delivery_id',
        'amount',
        'payment_method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_method' => P2pPaymentMethod::class,
            'status' => PaymentStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function p2pDelivery(): BelongsTo
    {
        return $this->belongsTo(P2pDelivery::class);
    }

    public function gateway(): HasOne
    {
        return $this->hasOne(P2pPaymentGateway::class);
    }
}
