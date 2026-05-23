<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminUserService
{
    use Paginatable;

    public function listAdmins()
    {
        return Admin::with('roles')->latest()->paginate($this->getPerPageLimit());
    }

    public function createAdmin(array $data): Admin
    {
        $data['password'] = Hash::make($data['password']);

        $admin = Admin::create($data);

        if (isset($data['roles'])) {
            $admin->syncRoles($data['roles']);
        }

        $admin->load('roles');

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
        if ($admin->id === 1) {
            throw new HttpException(403, 'Cannot delete the primary Super Admin account.');
        }

        $admin->delete();
    }
}
