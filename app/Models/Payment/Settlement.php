<?php

namespace App\Models\Payment;

use App\Builders\SettlementBuilder;
use App\Enums\SettlementStatus;
use App\Enums\SettlementType;
use App\Models\Courier\Courier;
use App\Models\Vendor\Vendor;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Settlement extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->where(function ($q) use ($zoneIds) {
            $q->where('settlement_type', SettlementType::COURIER->value)
                ->whereExists(function ($sub) use ($zoneIds) {
                    $sub->select(DB::raw(1))
                        ->from('couriers')
                        ->whereColumn('couriers.id', 'settlements.target_id')
                        ->whereIn('couriers.delivery_zone_id', $zoneIds);
                })
                ->orWhere('settlement_type', SettlementType::STORE->value)
                ->whereExists(function ($sub) use ($zoneIds) {
                    $sub->select(DB::raw(1))
                        ->from('vendors')
                        ->join('vendor_delivery_zones', 'vendors.id', '=', 'vendor_delivery_zones.vendor_id')
                        ->whereColumn('vendors.id', 'settlements.target_id')
                        ->whereIn('vendor_delivery_zones.delivery_zone_id', $zoneIds);
                });
        });
    }

    public $timestamps = false;

    protected $fillable = [
        'settlement_type',
        'target_id',
        'period_start',
        'period_end',
        'total_gross',
        'total_deductions',
        'total_net_exchange',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'settlement_type' => SettlementType::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'total_gross' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_net_exchange' => 'decimal:2',
            'status' => SettlementStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SettlementItem::class);
    }

    public function execution(): HasOne
    {
        return $this->hasOne(SettlementExecution::class);
    }

    public function note(): HasOne
    {
        return $this->hasOne(SettlementNote::class);
    }

    /**
     * Resolve the target entity (Courier or Vendor) based on settlement_type.
     */
    public function getTargetEntityAttribute(): Courier|Vendor|null
    {
        return match ($this->settlement_type) {
            SettlementType::COURIER => Courier::find($this->target_id),
            SettlementType::STORE => Vendor::find($this->target_id),
            default => null,
        };
    }

    public function newEloquentBuilder($query): SettlementBuilder
    {
        return new SettlementBuilder($query);
    }
}
