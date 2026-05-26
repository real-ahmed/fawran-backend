<?php

namespace App\Models\Product;

use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Builder;
use App\Traits\Scopes\AdminZoneScope;

class VendorMasterProductSubmission extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('vendor.deliveryZones', fn($q) => $q->whereIn('delivery_zones.id', $zoneIds));
    }

    protected $fillable = [
        'master_product_id',
        'vendor_id',
        'status',
        'reason',
    ];

    public function masterProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
