<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorItemInventory extends Model
{
    public $timestamps = false;

    protected $table = 'vendoritem_inventory';

    protected $primaryKey = 'vendoritem_id';

    public $incrementing = false;

    protected $fillable = [
        'vendoritem_id',
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
        return $this->belongsTo(VendorItem::class);
    }
}
