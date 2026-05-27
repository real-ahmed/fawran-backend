<?php

namespace App\Traits;

use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait HasPrimaryImage
{
    /**
     * Get all of the model's media.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    /**
     * Get the model's primary image url.
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function () {
                $primaryMedia = $this->media->where('is_primary', true)->first();

                if ($primaryMedia && !str_starts_with($primaryMedia->file_path, 'http')) {
                    return asset(\Illuminate\Support\Facades\Storage::disk('public')->url($primaryMedia->file_path));
                }

                return $primaryMedia ? $primaryMedia->file_path : null;
            }
        );
    }
}
