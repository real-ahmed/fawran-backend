<?php

namespace App\Models\Payment;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementNote extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'settlement_id';

    public $incrementing = false;

    protected $fillable = [
        'settlement_id',
        'notes',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }
}
