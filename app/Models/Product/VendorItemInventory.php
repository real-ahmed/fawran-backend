<?php

namespace App\Models\Product;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorItemInventory extends Model
{
    public $timestamps = false;

    protected $table = 'vendor_item_inventory';

    protected $primaryKey = 'vendor_item_id';

    public $incrementing = false;

    protected $fillable = [
        'vendor_item_id',
        'current_stock',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:3',
            'low_stock_threshold' => 'decimal:3',
        ];
    }

    public function storeItem(): BelongsTo
    {
        return $this->belongsTo(VendorItem::class, 'vendor_item_id');
    }
}
