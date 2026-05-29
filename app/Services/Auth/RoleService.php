<?php

namespace App\Services\Auth;

use App\DTOs\Auth\Role\RoleDataDTO;
use App\DTOs\Auth\Role\RoleFilterDTO;
use App\Models\Role;
use App\Traits\Paginatable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

class RoleService
{
    use Paginatable;

    public function getRoles(RoleFilterDTO $filters)
    {
        return Role::query()
            ->guard('api_admin')
            ->withPermissions()
            ->searchName($filters->search)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function getRoleById($id): Role
    {
        return Role::query()
            ->guard('api_admin')
            ->withPermissions()
            ->findOrFail($id);
    }

    public function createRole(RoleDataDTO $dto): Role
    {
        $role = Role::create([
            'name' => Str::slug($dto->display_name['en'] ?? 'role_'.time(), '_'),
            'display_name' => $dto->display_name,
            'guard_name' => 'api_admin',
        ]);

        if ($dto->permissions !== null) {
            $role->syncPermissions($dto->permissions);
        }

        return $role->load('permissions');
    }

    public function updateRole(Role $role, RoleDataDTO $dto): Role
    {
        if ($role->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'name' => __('messages.cannot_modify_super_admin_role'),
            ]);
        }

        if (! empty($dto->display_name)) {
            $role->display_name = $dto->display_name;
            $role->save();
        }

        if ($dto->permissions !== null) {
            $role->syncPermissions($dto->permissions);
        }

        return $role->load('permissions');
    }

    public function deleteRole(Role $role): void
    {
        if ($role->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'name' => __('messages.cannot_delete_super_admin_role'),
            ]);
        }

        $role->delete();
    }

    public function getAllPermissions()
    {
        return Permission::where('guard_name', 'api_admin')->get()->map(function ($permission) {
            $permission->display_name = [
                'en' => trans('permissions.'.$permission->name, [], 'en'),
                'ar' => trans('permissions.'.$permission->name, [], 'ar'),
            ];

            return $permission;
        })->groupBy(function ($permission) {
            // Group by the noun (e.g. "delivery zones" from "view delivery zones")
            $parts = explode(' ', $permission->name, 2);

            return $parts[1] ?? 'general';
        });
    }
}
