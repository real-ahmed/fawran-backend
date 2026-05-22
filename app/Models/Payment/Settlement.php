<?php

namespace App\Models\Payment;

use App\Enums\SettlementStatus;
use App\Enums\SettlementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Settlement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'settlement_type',
        'target_id',
        'period_start',
        'period_end',
        'total_gross',
        'total_deductions',
        'total_net_exchange',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'settlement_type' => SettlementType::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'total_gross' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_net_exchange' => 'decimal:2',
            'status' => SettlementStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SettlementItem::class);
    }

    public function execution(): HasOne
    {
        return $this->hasOne(SettlementExecution::class);
    }

    public function note(): HasOne
    {
        return $this->hasOne(SettlementNote::class);
    }
}
