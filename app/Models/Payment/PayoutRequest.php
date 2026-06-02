<?php

namespace App\Models\Payment;

use App\Builders\PayoutRequestBuilder;
use App\Enums\PayoutRequestStatus;
use App\Models\User;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayoutRequest extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->where(function (Builder $query) use ($zoneIds): void {
            $this->whereCourierLocationInAdminZones($query, $zoneIds, 'payout_requests.user_id', 'couriers.user_id');

            $query->orWhereExists(function ($sub) use ($zoneIds): void {
                $sub->selectRaw('1')
                    ->from('vendors')
                    ->join('vendor_delivery_zones', 'vendors.id', '=', 'vendor_delivery_zones.vendor_id')
                    ->whereColumn('vendors.owner_id', 'payout_requests.user_id')
                    ->whereIn('vendor_delivery_zones.delivery_zone_id', $zoneIds);
            });
        });
    }

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'amount',
        'bank_details',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bank_details' => 'encrypted',
            'status' => PayoutRequestStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function execution(): HasOne
    {
        return $this->hasOne(PayoutExecution::class);
    }

    public function newEloquentBuilder($query): PayoutRequestBuilder
    {
        return new PayoutRequestBuilder($query);
    }
}
