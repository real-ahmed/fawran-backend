<?php

namespace App\Models\Vendor;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorDescription extends Model
{
    public $timestamps = false;

    protected $table = 'vendordescriptions';

    protected $primaryKey = 'vendor_id';

    public $incrementing = false;

    protected $fillable = [
        'vendor_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'description' => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
