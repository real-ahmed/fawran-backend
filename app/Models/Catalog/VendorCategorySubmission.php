<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class VendorCategorySubmission extends Model
{
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
