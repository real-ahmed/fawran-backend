<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Notifications\Admin\AdminCredentialsGeneratedNotification;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorOwnerService
{
    /**
     * List users that can be vendor owners.
     */
    public function listOwners(array $filters = []): CursorPaginator
    {
        return User::query()
            ->searchIdentity($filters['search'] ?? null)
            ->newest()
            ->cursorPaginate(15);
    }

    /**
     * Create a new vendor owner (user).
     */
    public function createOwner(array $data): User
    {
        $plainPassword = Str::password(10);
        $data['password'] = Hash::make($plainPassword);

        $user = User::create($data);

        // Notify the user with their auto-generated credentials
        $user->notify(new AdminCredentialsGeneratedNotification($plainPassword));

        return $user;
    }
}
