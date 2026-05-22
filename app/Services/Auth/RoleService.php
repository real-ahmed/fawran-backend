<?php

namespace App\Services\Auth;

use App\Traits\Paginatable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\ValidationException;

class RoleService
{
    use Paginatable;

    public function getRoles(array $filters)
    {
        $query = Role::query()->where('guard_name', 'api_admin')->with('permissions');

        if (!empty($filters['search'])) {
            $query->where('name', 'LIKE', '%' . $filters['search'] . '%');
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
            'name' => $data['name'],
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
                'name' => 'The Super Admin role cannot be modified.',
            ]);
        }

        if (isset($data['name'])) {
            $role->name = $data['name'];
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
                'name' => 'The Super Admin role cannot be deleted.',
            ]);
        }

        $role->delete();
    }

    public function getAllPermissions()
    {
        return Permission::where('guard_name', 'api_admin')->get()->groupBy(function ($permission) {
            // Group by the noun (e.g. "delivery zones" from "view delivery zones")
            $parts = explode(' ', $permission->name, 2);
            return $parts[1] ?? 'general';
        });
    }
}
