<?php

namespace App\Models\Product;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantDishDetail extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'vendor_item_id';

    public $incrementing = false;

    protected $fillable = [
        'vendor_item_id',
        'preparation_time',
    ];

    protected function casts(): array
    {
        return [
            'preparation_time' => 'integer',
        ];
    }

    public function storeItem(): BelongsTo
    {
        return $this->belongsTo(VendorItem::class);
    }
}
