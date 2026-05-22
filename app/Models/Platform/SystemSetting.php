<?php

namespace App\Models\Platform;

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
}
