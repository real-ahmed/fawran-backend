<?php

namespace App\Models\Vendor;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorCustomCommission extends Model
{
    public $timestamps = false;

    protected $table = 'vendorcustom_commissions';

    protected $primaryKey = 'vendor_id';

    public $incrementing = false;

    protected $fillable = [
        'vendor_id',
        'commission_percentage',
    ];

    protected function casts(): array
    {
        return [
            'commission_percentage' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
