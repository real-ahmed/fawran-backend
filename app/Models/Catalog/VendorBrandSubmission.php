<?php

namespace App\Models\Catalog;

use App\Models\Vendor\Vendor;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VendorBrandSubmission extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('vendor.deliveryZones', fn ($q) => $q->whereIn('delivery_zones.id', $zoneIds));
    }

    protected $primaryKey = 'brand_id';

    public $incrementing = false;

    protected $fillable = [
        'brand_id',
        'vendor_id',
        'status',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
