<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreItemInventory extends Model
{
    public $timestamps = false;

    protected $table = 'store_item_inventory';

    protected $primaryKey = 'store_item_id';

    public $incrementing = false;

    protected $fillable = [
        'store_item_id',
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
        return $this->belongsTo(StoreItem::class);
    }
}
