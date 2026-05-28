<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Traits\Paginatable;
use Illuminate\Http\Request;

class CustomerService
{
    use Paginatable;

    public function listCustomers(Request $request)
    {
        return User::query()
            ->searchIdentity($request->query('search'))
            ->active($request->has('is_active') ? $request->boolean('is_active') : null)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function getCustomer(User $user): User
    {
        return $user->load(['wallet', 'addresses']);
    }

    public function toggleStatus(User $user, bool $isActive): User
    {
        $user->update(['is_active' => $isActive]);

        return $user;
    }
}
