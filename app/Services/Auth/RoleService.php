<?php

namespace App\Services\Auth;

use App\Models\Role;
use App\Traits\Paginatable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

class RoleService
{
    use Paginatable;

    public function getRoles(array $filters)
    {
        $query = Role::query()->where('guard_name', 'api_admin')->with('permissions');

        if (! empty($filters['search'])) {
            $query->where('name', 'LIKE', '%'.$filters['search'].'%');
        }

        return $query->paginate($this->getPerPageLimit($filters['per_page'] ?? null));
    }

    public function getRoleById($id): Role
    {
        return Role::where('guard_name', 'api_admin')->with('permissions')->findOrFail($id);
    }

    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name' => Str::slug($data['display_name']['en'] ?? 'role_'.time(), '_'),
            'display_name' => $data['display_name'] ?? null,
            'guard_name' => 'api_admin',
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function updateRole(Role $role, array $data): Role
    {
        if ($role->name === 'Super Admin') {
            throw ValidationException::withMessages([
                'name' => __('messages.cannot_modify_super_admin_role'),
            ]);
        }

        if (isset($data['display_name'])) {
            $role->display_name = $data['display_name'];
            $role->save();
        }

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function deleteRole(Role $role): void
    {
        if ($role->name === 'Super Admin') {
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
