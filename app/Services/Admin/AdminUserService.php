<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Models\Role;
use App\Notifications\Admin\AdminCredentialsGeneratedNotification;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminUserService
{
    use Paginatable;

    public function listAdmins(?string $search = null)
    {
        return Admin::query()
            ->withRoles()
            ->searchIdentity($search)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function createAdmin(array $data): Admin
    {
        if (isset($data['roles']) && $this->containsSuperAdminRole($data['roles'])) {
            throw ValidationException::withMessages([
                'roles' => __('messages.cannot_assign_super_admin_role'),
            ]);
        }

        $plainPassword = Str::password(10);
        $data['password'] = Hash::make($plainPassword);

        $admin = Admin::create($data);

        if (isset($data['roles'])) {
            $admin->syncRoles($data['roles']);
        }

        if (isset($data['delivery_zones'])) {
            $admin->deliveryZones()->sync($data['delivery_zones']);
        }

        $admin->load('roles', 'deliveryZones');

        $admin->notify(new AdminCredentialsGeneratedNotification($plainPassword));

        return $admin;
    }

    public function updateAdmin(Admin $admin, array $data): Admin
    {
        $this->ensureSuperAdminRolesCanBeSynced($admin, $data);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $admin->update($data);

        if (isset($data['roles'])) {
            $admin->syncRoles($data['roles']);
        }

        if (isset($data['delivery_zones'])) {
            $admin->deliveryZones()->sync($data['delivery_zones']);
        }

        $admin->load('roles', 'deliveryZones');

        return $admin;
    }

    public function deleteAdmin(Admin $admin): void
    {
        if ($admin->isSuperAdmin()) {
            throw new HttpException(403, __('messages.cannot_delete_super_admin'));
        }

        $admin->delete();
    }

    private function ensureSuperAdminRolesCanBeSynced(Admin $admin, array $data): void
    {
        if (! isset($data['roles'])) {
            return;
        }

        if ($admin->isSuperAdmin() && ! $this->containsOnlySuperAdminRole($data['roles'])) {
            throw ValidationException::withMessages([
                'roles' => __('messages.cannot_change_super_admin_account_role'),
            ]);
        }

        if (! $admin->isSuperAdmin() && $this->containsSuperAdminRole($data['roles'])) {
            throw ValidationException::withMessages([
                'roles' => __('messages.cannot_assign_super_admin_role'),
            ]);
        }
    }

    private function containsOnlySuperAdminRole(array $roles): bool
    {
        return count($roles) === 1 && $this->containsSuperAdminRole($roles);
    }

    private function containsSuperAdminRole(array $roles): bool
    {
        return collect($roles)->contains(
            fn (string $role): bool => Role::isSuperAdminName($role)
        );
    }
}
