<?php

namespace App\Models\Product;

use App\Models\Catalog\Brand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailProductDetail extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'master_product_id';

    public $incrementing = false;

    protected $fillable = [
        'master_product_id',
        'brand_id',
        'sku_barcode',
    ];

    public function masterProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
