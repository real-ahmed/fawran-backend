<?php

namespace App\Models\Payment;

use App\Models\Courier\Courier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CourierCashCollection extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'courier_id',
        'source_type',
        'source_id',
        'amount_collected',
        'courier_fee_share',
        'amount_owed_to_platform',
        'is_settled',
        'collected_at',
    ];

    protected $attributes = [
        'is_settled' => false,
    ];

    protected function casts(): array
    {
        return [
            'amount_collected' => 'decimal:2',
            'courier_fee_share' => 'decimal:2',
            'amount_owed_to_platform' => 'decimal:2',
            'is_settled' => 'boolean',
            'collected_at' => 'datetime',
        ];
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
