<?php

namespace App\Models\Vendor;

use App\Models\Model;
use App\Models\User;
use Database\Factories\VendorStaffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorStaff extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'vendorstaff';

    protected static function newFactory(): VendorStaffFactory
    {
        return VendorStaffFactory::new();
    }

    protected $fillable = [
        'user_id',
        'vendor_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
