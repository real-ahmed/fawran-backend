<?php

namespace App\Models\Auth;

use App\Enums\SocialProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_id',
    ];

    protected function casts(): array
    {
        return [
            'provider_name' => SocialProvider::class,
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
