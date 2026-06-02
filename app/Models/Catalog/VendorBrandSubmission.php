<?php

namespace App\Models\Catalog;

use App\Builders\VendorBrandSubmissionBuilder;
use App\Models\Vendor\Vendor;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBrandSubmission extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('vendor.deliveryZones', fn ($query) => $query->whereIn('delivery_zone_id', $zoneIds));
    }

    protected $primaryKey = 'brand_id';

    public $incrementing = false;

    protected $fillable = [
        'brand_id',
        'vendor_id',
        'status',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function newEloquentBuilder($query): VendorBrandSubmissionBuilder
    {
        return new VendorBrandSubmissionBuilder($query);
    }
}
