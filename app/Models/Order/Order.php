<?php

namespace App\Models\Order;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Media\Rating;
use App\Models\Payment\Payment;
use App\Models\Payment\RefundRequest;
use App\Models\Platform\OrderCommission;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('orderDelivery', fn ($q) => $q->whereIn('delivery_zone_id', $zoneIds));
    }

    protected $fillable = [
        'order_type',
        'total_products',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'order_type' => OrderType::class,
            'total_products' => 'decimal:2',
            'status' => OrderStatus::class,
        ];
    }

    public function customer(): HasOne
    {
        return $this->hasOne(OrderCustomer::class);
    }

    public function orderDelivery(): HasOne
    {
        return $this->hasOne(OrderDelivery::class);
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(OrderCommission::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }
}
