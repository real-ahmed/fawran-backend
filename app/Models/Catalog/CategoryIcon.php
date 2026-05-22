<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryIcon extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'category_id';

    public $incrementing = false;

    protected $fillable = [
        'category_id',
        'icon_class',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
