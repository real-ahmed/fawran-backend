<?php

namespace App\Models\Catalog;

use App\Builders\VendorCategorySubmissionBuilder;
use App\Models\Model;
use App\Models\Vendor\Vendor;
use App\Traits\Scopes\AdminZoneScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorCategorySubmission extends Model
{
    use AdminZoneScope;

    protected function applyZoneFilter(Builder $query, array $zoneIds): void
    {
        $query->whereHas('vendor.deliveryZones', fn ($query) => $query->whereIn('delivery_zone_id', $zoneIds));
    }

    protected $primaryKey = 'category_id';

    public $incrementing = false;

    protected $fillable = [
        'category_id',
        'vendor_id',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function newEloquentBuilder($query): VendorCategorySubmissionBuilder
    {
        return new VendorCategorySubmissionBuilder($query);
    }
}
