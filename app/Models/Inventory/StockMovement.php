<?php

namespace App\Models\Inventory;

use App\Enums\StockMovementType;
use App\Models\Product\VendorItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'vendor_item_id',
        'quantity',
        'type',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'type' => StockMovementType::class,
            'created_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function storeItem(): BelongsTo
    {
        return $this->belongsTo(VendorItem::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function note(): HasOne
    {
        return $this->hasOne(StockMovementNote::class);
    }
}
