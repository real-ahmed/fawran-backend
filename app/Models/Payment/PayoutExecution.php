<?php

namespace App\Models\Payment;

use App\Models\Admin;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutExecution extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'payout_request_id';

    public $incrementing = false;

    protected $fillable = [
        'payout_request_id',
        'admin_id',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
        ];
    }

    public function payoutRequest(): BelongsTo
    {
        return $this->belongsTo(PayoutRequest::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
