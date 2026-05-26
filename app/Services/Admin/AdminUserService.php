<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Notifications\Admin\AdminCredentialsGenerated;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminUserService
{
    use Paginatable;

    public function listAdmins(?string $search = null)
    {
        $query = Admin::with('roles')->latest();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate($this->getPerPageLimit());
    }

    public function createAdmin(array $data): Admin
    {
        $plainPassword = Str::password(10);
        $data['password'] = Hash::make($plainPassword);

        $admin = Admin::create($data);

        if (isset($data['roles'])) {
            $admin->syncRoles($data['roles']);
        }

        $admin->load('roles');

        $admin->notify(new AdminCredentialsGenerated($plainPassword));

        return $admin;
    }

    public function updateAdmin(Admin $admin, array $data): Admin
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $admin->update($data);

        if (isset($data['roles'])) {
            $admin->syncRoles($data['roles']);
        }

        $admin->load('roles');

        return $admin;
    }

    public function deleteAdmin(Admin $admin): void
    {
        if ($admin->id === 1 || $admin->hasRole('Super Admin')) {
            throw new HttpException(403, __('messages.cannot_delete_super_admin'));
        }

        $admin->delete();
    }
}
