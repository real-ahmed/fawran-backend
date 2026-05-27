<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Notifications\Admin\AdminCredentialsGenerated;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorOwnerService
{
    /**
     * List users that can be vendor owners.
     */
    public function listOwners(array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->searchIdentity($filters['search'] ?? null)
            ->newest()
            ->paginate(15);
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
        $user->notify(new AdminCredentialsGenerated($plainPassword));

        return $user;
    }
}
