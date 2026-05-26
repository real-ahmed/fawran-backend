<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class VendorBrandSubmission extends Model
{
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
        return $this->belongsTo(\App\Models\Vendor\Vendor::class);
    }
}
