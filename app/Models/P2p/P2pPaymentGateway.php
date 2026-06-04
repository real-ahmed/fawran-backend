<?php

namespace App\Models\P2p;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2pPaymentGateway extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'p2p_payment_id';

    public $incrementing = false;

    protected $fillable = [
        'p2p_payment_id',
        'gateway_transaction_id',
    ];

    public function p2pPayment(): BelongsTo
    {
        return $this->belongsTo(P2pPayment::class);
    }
}
