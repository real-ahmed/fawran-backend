<?php

namespace App\Models\P2p;

use App\Enums\P2pDeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class P2pStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'p2p_delivery_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => P2pDeliveryStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function p2pDelivery(): BelongsTo
    {
        return $this->belongsTo(P2pDelivery::class);
    }

    public function note(): HasOne
    {
        return $this->hasOne(P2pStatusLogNote::class);
    }
}
