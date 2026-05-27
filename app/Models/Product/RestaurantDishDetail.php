<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantDishDetail extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'vendoritem_id';

    public $incrementing = false;

    protected $fillable = [
        'vendoritem_id',
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
