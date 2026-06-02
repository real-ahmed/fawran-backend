<?php

namespace App\Models\Platform;

use App\Models\Order\Order;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCommission extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('order.orderDelivery', fn ($q) => $q->whereIn('delivery_zone_id', $zoneIds));
    }

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'vendorcommission_percentage',
        'vendorcommission_amount',
        'app_delivery_share',
        'net_platform_profit',
    ];

    protected function casts(): array
    {
        return [
            'vendorcommission_percentage' => 'decimal:2',
            'vendorcommission_amount' => 'decimal:2',
            'app_delivery_share' => 'decimal:2',
            'net_platform_profit' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
