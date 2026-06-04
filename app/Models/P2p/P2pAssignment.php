<?php

namespace App\Models\P2p;

use App\Models\Courier\Courier;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2pAssignment extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'p2p_delivery_id';

    public $incrementing = false;

    protected $fillable = [
        'p2p_delivery_id',
        'courier_id',
        'fee_share',
    ];

    protected function casts(): array
    {
        return [
            'fee_share' => 'decimal:2',
        ];
    }

    public function p2pDelivery(): BelongsTo
    {
        return $this->belongsTo(P2pDelivery::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }
}
