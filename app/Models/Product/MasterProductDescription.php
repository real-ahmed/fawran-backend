<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterProductDescription extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'master_product_id';

    public $incrementing = false;

    protected $fillable = [
        'master_product_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'description' => 'array',
        ];
    }

    public function masterProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class);
    }
}
