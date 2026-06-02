<?php

namespace App\Services\Admin;

use App\DTOs\Admin\AdminUserDataDTO;
use App\DTOs\Admin\AdminUserFilterDTO;
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

    public function __construct(private AdminZoneService $adminZoneService) {}

    public function listAdmins(AdminUserFilterDTO $filters)
    {
        return Admin::query()
            ->forAdminZones()
            ->withRoles()
            ->searchIdentity($filters->search)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function createAdmin(AdminUserDataDTO $dto): Admin
    {
        $this->adminZoneService->ensureZoneIdsAssignable($dto->delivery_zones);

        if (! is_null($dto->roles) && $this->containsSuperAdminRole($dto->roles)) {
            throw ValidationException::withMessages([
                'roles' => __('messages.cannot_assign_super_admin_role'),
            ]);
        }

        $plainPassword = Str::password(10);

        $data = $dto->toArray();
        $data['password'] = Hash::make($plainPassword);

        $admin = Admin::create($data);

        if (! is_null($dto->roles)) {
            $admin->syncRoles($dto->roles);
        }

        if (! is_null($dto->delivery_zones)) {
            $admin->deliveryZones()->sync($dto->delivery_zones);
        }

        $admin->load('roles', 'deliveryZones');

        $admin->notify(new AdminCredentialsGeneratedNotification($plainPassword));

        return $admin;
    }

    public function getAdmin(Admin $admin): Admin
    {
        $admin->ensureVisibleToAdminZones();

        return $admin->load('roles');
    }

    public function updateAdmin(Admin $admin, AdminUserDataDTO $dto): Admin
    {
        $admin->ensureVisibleToAdminZones();

        $this->ensureSuperAdminRolesCanBeSynced($admin, $dto->roles);
        $this->adminZoneService->ensureZoneIdsAssignable($dto->delivery_zones);

        $data = $dto->toArray();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $admin->update($data);

        if (! is_null($dto->roles)) {
            $admin->syncRoles($dto->roles);
        }

        if (! is_null($dto->delivery_zones)) {
            $admin->deliveryZones()->sync($dto->delivery_zones);
        }

        $admin->load('roles', 'deliveryZones');

        return $admin;
    }

    public function deleteAdmin(Admin $admin): void
    {
        $admin->ensureVisibleToAdminZones();

        if ($admin->isSuperAdmin()) {
            throw new HttpException(403, __('messages.cannot_delete_super_admin'));
        }

        $admin->delete();
    }

    private function ensureSuperAdminRolesCanBeSynced(Admin $admin, ?array $roles): void
    {
        if (is_null($roles)) {
            return;
        }

        if ($admin->isSuperAdmin() && ! $this->containsOnlySuperAdminRole($roles)) {
            throw ValidationException::withMessages([
                'roles' => __('messages.cannot_change_super_admin_account_role'),
            ]);
        }

        if (! $admin->isSuperAdmin() && $this->containsSuperAdminRole($roles)) {
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
