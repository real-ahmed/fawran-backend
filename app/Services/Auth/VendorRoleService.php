<?php

namespace App\Services\Auth;

use App\Enums\VendorPermission;
use App\Models\Role;
use App\Traits\Paginatable;
use Spatie\Permission\Models\Permission;

class VendorRoleService
{
    use Paginatable;

    public function getRoles(int $vendorId, array $filters)
    {
        return Role::query()
            ->vendor($vendorId)
            ->withPermissions()
            ->searchName($filters['search'] ?? null)
            ->paginate($this->getPerPageLimit($filters['per_page'] ?? null));
    }

    public function getRoleById(int $vendorId, $id): Role
    {
        return Role::query()
            ->vendor($vendorId)
            ->withPermissions()
            ->findOrFail($id);
    }

    public function createRole(int $vendorId, array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'api', // Default guard for store users
            'vendor_id' => $vendorId,
        ]);

        if (isset($data['permissions'])) {
            // Filter only valid store permissions
            $validPermissions = array_intersect($data['permissions'], VendorPermission::values());

            // Ensure permissions exist in DB for vendor_id 0 (global definition)
            $permissionIds = [];
            foreach ($validPermissions as $permName) {
                $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'api']);
                $permissionIds[] = $permission->id;
            }

            $role->syncPermissions($permissionIds);
        }

        return $role->load('permissions');
    }

    public function updateRole(int $vendorId, Role $role, array $data): Role
    {
        if ($role->vendor_id !== $vendorId) {
            abort(403, 'Unauthorized action.');
        }

        if (isset($data['name'])) {
            $role->name = $data['name'];
            $role->save();
        }

        if (isset($data['permissions'])) {
            $validPermissions = array_intersect($data['permissions'], VendorPermission::values());

            $permissionIds = [];
            foreach ($validPermissions as $permName) {
                $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'api']);
                $permissionIds[] = $permission->id;
            }

            $role->syncPermissions($permissionIds);
        }

        return $role->load('permissions');
    }

    public function deleteRole(int $vendorId, Role $role): void
    {
        if ($role->vendor_id !== $vendorId) {
            abort(403, 'Unauthorized action.');
        }

        $role->delete();
    }

    public function getAllPermissions()
    {
        // Group store permissions logically for frontend
        $permissions = collect(VendorPermission::values());

        return $permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission, 3);

            return $parts[2] ?? 'general'; // e.g. "view store products" -> "products"
        });
    }
}
