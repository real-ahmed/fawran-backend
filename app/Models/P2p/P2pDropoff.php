<?php

namespace App\Models\P2p;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2pDropoff extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'p2p_delivery_id';

    public $incrementing = false;

    protected $fillable = [
        'p2p_delivery_id',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
        ];
    }

    public function p2pDelivery(): BelongsTo
    {
        return $this->belongsTo(P2pDelivery::class);
    }
}
