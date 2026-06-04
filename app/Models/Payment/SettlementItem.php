<?php

namespace App\Models\Payment;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SettlementItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'settlement_id',
        'reference_type',
        'reference_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
