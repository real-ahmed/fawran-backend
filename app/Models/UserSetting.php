<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public function settingable(): MorphTo
    {
        return $this->morphTo();
    }
}
