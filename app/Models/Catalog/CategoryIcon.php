<?php

namespace App\Models\Catalog;

use App\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CategoryIcon extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'category_id';

    public $incrementing = false;

    protected $fillable = [
        'category_id',
        'icon_path',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function iconUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->icon_path ? asset(Storage::disk('public')->url($this->icon_path)) : null
        );
    }
}
