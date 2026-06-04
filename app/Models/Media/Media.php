<?php

namespace App\Models\Media;

use App\Enums\FileType;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'model_type',
        'model_id',
        'file_path',
        'file_type',
        'order',
        'is_primary',
    ];

    protected $attributes = [
        'order' => 0,
        'is_primary' => false,
    ];

    protected function casts(): array
    {
        return [
            'file_type' => FileType::class,
            'order' => 'integer',
            'is_primary' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
