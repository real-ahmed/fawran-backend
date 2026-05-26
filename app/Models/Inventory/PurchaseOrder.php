<?php

namespace App\Models\Inventory;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'supplier_id',
        'total_cost',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_cost' => 'decimal:2',
            'status' => PurchaseOrderStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
