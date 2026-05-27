<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $imageKeys = ['app_logo', 'app_logo_white', 'app_icon', 'favicon'];

                if (in_array($this->key, $imageKeys) && ! empty($value) && ! str_starts_with($value, 'http')) {
                    // Strip legacy '/storage/' prefix if present
                    $cleanValue = preg_replace('/^\/?storage\//', '', $value);
                    return asset(\Illuminate\Support\Facades\Storage::disk('public')->url($cleanValue));
                }

                return $value;
            }
        );
    }
}
