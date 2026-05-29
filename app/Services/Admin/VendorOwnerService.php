<?php

namespace App\Services\Admin;

use App\DTOs\Admin\Vendor\VendorOwnerDataDTO;
use App\DTOs\Admin\Vendor\VendorOwnerFilterDTO;
use App\Models\User;
use App\Notifications\Admin\AdminCredentialsGeneratedNotification;
use App\Traits\Paginatable;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorOwnerService
{
    use Paginatable;

    /**
     * List users that can be vendor owners.
     */
    public function listOwners(VendorOwnerFilterDTO $filters): CursorPaginator
    {
        return User::query()
            ->searchIdentity($filters->search)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    /**
     * Create a new vendor owner (user).
     */
    public function createOwner(VendorOwnerDataDTO $dto): User
    {
        $plainPassword = Str::password(10);

        $data = [
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'password' => Hash::make($plainPassword),
        ];

        if ($dto->is_active !== null) {
            $data['is_active'] = $dto->is_active;
        }

        $user = User::create($data);

        // Notify the user with their auto-generated credentials
        $user->notify(new AdminCredentialsGeneratedNotification($plainPassword));

        return $user;
    }
}
