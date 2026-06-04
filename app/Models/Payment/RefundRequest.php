<?php

namespace App\Models\Payment;

use App\Builders\RefundRequestBuilder;
use App\Enums\RefundRequestStatus;
use App\Enums\RefundResolution;
use App\Models\Model;
use App\Models\Order\Order;
use App\Models\User;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefundRequest extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('order.orderDelivery', fn ($q) => $q->whereIn('delivery_zone_id', $zoneIds));
    }

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'order_id',
        'total_amount',
        'reason',
        'resolution',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'resolution' => RefundResolution::class,
            'status' => RefundRequestStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RefundItem::class);
    }

    public function newEloquentBuilder($query): RefundRequestBuilder
    {
        return new RefundRequestBuilder($query);
    }
}
