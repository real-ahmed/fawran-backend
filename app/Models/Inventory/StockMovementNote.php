<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementNote extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'stock_movement_id';

    public $incrementing = false;

    protected $fillable = [
        'stock_movement_id',
        'notes',
    ];

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }
}
