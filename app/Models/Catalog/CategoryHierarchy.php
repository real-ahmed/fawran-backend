<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryHierarchy extends Model
{
    public $timestamps = false;

    protected $table = 'category_hierarchies';

    protected $primaryKey = 'child_category_id';

    public $incrementing = false;

    protected $fillable = [
        'child_category_id',
        'parent_category_id',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'child_category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }
}
