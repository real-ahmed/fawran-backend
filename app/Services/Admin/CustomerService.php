<?php

namespace App\Services\Admin;

use App\DTOs\Admin\Customer\CustomerFilterDTO;
use App\Models\User;
use App\Traits\Paginatable;

class CustomerService
{
    use Paginatable;

    public function listCustomers(CustomerFilterDTO $filters)
    {
        return User::query()
            ->searchIdentity($filters->search)
            ->active($filters->is_active)
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
