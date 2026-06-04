<?php

namespace App\Models\Payment;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'balance',
    ];

    protected $attributes = [
        'balance' => 0.00,
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
