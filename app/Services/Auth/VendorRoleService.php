<?php

namespace App\Services\Auth;

use App\DTOs\Auth\VendorRole\VendorRoleDataDTO;
use App\DTOs\Auth\VendorRole\VendorRoleFilterDTO;
use App\Enums\VendorPermission;
use App\Models\Role;
use App\Traits\Paginatable;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class VendorRoleService
{
    use Paginatable;

    public function resolveVendorId(Request $request): int
    {
        $vendorId = (int) getPermissionsTeamId();

        if ($vendorId > 0) {
            return $vendorId;
        }

        $vendorId = $request->header('X-VENDOR-ID') ?? $request->query('vendor_id');
        abort_if(! $vendorId, 400, 'Vendor ID is required in header or query parameter.');

        return (int) $vendorId;
    }

    public function getRoles(int $vendorId, VendorRoleFilterDTO $filters): CursorPaginator
    {
        return Role::query()
            ->vendor($vendorId)
            ->withPermissions()
            ->searchName($filters->search)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function getRoleById(int $vendorId, int|string $id): Role
    {
        return Role::query()
            ->vendor($vendorId)
            ->withPermissions()
            ->findOrFail($id);
    }

    public function createRole(int $vendorId, VendorRoleDataDTO $dto): Role
    {
        $role = Role::create([
            'name' => $dto->name,
            'guard_name' => 'api', // Default guard for store users
            'vendor_id' => $vendorId,
        ]);

        if ($dto->permissions !== null) {
            // Filter only valid store permissions
            $validPermissions = array_intersect($dto->permissions, VendorPermission::values());

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

    public function updateRole(int $vendorId, Role $role, VendorRoleDataDTO $dto): Role
    {
        if ($role->vendor_id !== $vendorId) {
            abort(403, 'Unauthorized action.');
        }

        if ($dto->name !== null) {
            $role->name = $dto->name;
            $role->save();
        }

        if ($dto->permissions !== null) {
            $validPermissions = array_intersect($dto->permissions, VendorPermission::values());

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

    public function getAllPermissions(): Collection
    {
        // Group store permissions logically for frontend
        $permissions = collect(VendorPermission::values());

        return $permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission, 3);

            return $parts[2] ?? 'general'; // e.g. "view store products" -> "products"
        });
    }
}
