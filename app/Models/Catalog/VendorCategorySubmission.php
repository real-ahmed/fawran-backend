<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;

class VendorCategorySubmission extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('vendor.deliveryZones', fn($q) => $q->whereIn('delivery_zones.id', $zoneIds));
    }

    protected $primaryKey = 'category_id';
    public $incrementing = false;

    protected $fillable = [
        'category_id',
        'vendor_id',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\Vendor\Vendor::class);
    }
}
