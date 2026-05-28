<?php

namespace App\Traits;

use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

trait HasImages
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

                if ($primaryMedia && ! str_starts_with($primaryMedia->file_path, 'http')) {
                    return asset(Storage::disk('public')->url($primaryMedia->file_path));
                }

                return $primaryMedia ? $primaryMedia->file_path : null;
            }
        );
    }

    /**
     * Get the model's non-primary images urls.
     */
    protected function images(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->media->where('is_primary', false)->map(function ($media) {
                    if (! str_starts_with($media->file_path, 'http')) {
                        return asset(Storage::disk('public')->url($media->file_path));
                    }

                    return $media->file_path;
                })->values()->all();
            }
        );
    }
}
