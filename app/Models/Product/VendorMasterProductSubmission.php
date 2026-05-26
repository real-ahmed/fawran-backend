<?php

namespace App\Models\Product;

use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorMasterProductSubmission extends Model
{
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
