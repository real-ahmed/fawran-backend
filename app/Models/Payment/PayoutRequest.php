<?php

namespace App\Models\Payment;

use App\Enums\PayoutRequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayoutRequest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'amount',
        'bank_details',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bank_details' => 'encrypted',
            'status' => PayoutRequestStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function execution(): HasOne
    {
        return $this->hasOne(PayoutExecution::class);
    }
}
