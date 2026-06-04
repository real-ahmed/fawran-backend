<?php

namespace App\Models\Order;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryDropoff extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'delivery_id';

    public $incrementing = false;

    protected $fillable = [
        'delivery_id',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
