<?php

namespace App\Models\Inventory;

use App\Models\Model;
use App\Models\Product\VendorItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_order_id',
        'vendor_item_id',
        'quantity',
        'cost_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'cost_price' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function storeItem(): BelongsTo
    {
        return $this->belongsTo(VendorItem::class);
    }
}
