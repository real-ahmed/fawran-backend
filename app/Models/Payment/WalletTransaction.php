<?php

namespace App\Models\Payment;

use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'type' => WalletTransactionType::class,
            'created_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
