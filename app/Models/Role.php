<?php

namespace App\Models;

use App\Builders\RoleBuilder;
use App\Traits\HasSystemTimezoneDates;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasSystemTimezoneDates;

    public const SUPER_ADMIN_NAME = 'Super Admin';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'display_name' => 'array',
    ];

    public static function isSuperAdminName(string $name): bool
    {
        return Str::of($name)
            ->replace(['_', '-'], ' ')
            ->squish()
            ->lower()
            ->toString() === 'super admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->id === 1 || self::isSuperAdminName($this->name);
    }

    public function newEloquentBuilder($query): RoleBuilder
    {
        return new RoleBuilder($query);
    }
}
