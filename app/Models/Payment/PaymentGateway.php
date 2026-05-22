<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGateway extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'payment_id';

    public $incrementing = false;

    protected $fillable = [
        'payment_id',
        'gateway_transaction_id',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
