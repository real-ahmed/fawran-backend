<?php

namespace App\Models\Product;

use App\Builders\VendorMasterProductSubmissionBuilder;
use App\Models\Vendor\Vendor;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorMasterProductSubmission extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('vendor.deliveryZones', fn ($q) => $q->whereIn('delivery_zones.id', $zoneIds));
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

    public function newEloquentBuilder($query): VendorMasterProductSubmissionBuilder
    {
        return new VendorMasterProductSubmissionBuilder($query);
    }
}
