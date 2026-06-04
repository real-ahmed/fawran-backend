<?php

namespace App\Models\Payment;

use App\Enums\ExecutionMethod;
use App\Models\Admin;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementExecution extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'settlement_id';

    public $incrementing = false;

    protected $fillable = [
        'settlement_id',
        'admin_id',
        'execution_method',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'execution_method' => ExecutionMethod::class,
            'executed_at' => 'datetime',
        ];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
