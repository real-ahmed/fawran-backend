<?php

namespace App\Models\P2p;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2pPickup extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'p2p_delivery_id';

    public $incrementing = false;

    protected $fillable = [
        'p2p_delivery_id',
        'picked_up_at',
    ];

    protected function casts(): array
    {
        return [
            'picked_up_at' => 'datetime',
        ];
    }

    public function p2pDelivery(): BelongsTo
    {
        return $this->belongsTo(P2pDelivery::class);
    }
}
