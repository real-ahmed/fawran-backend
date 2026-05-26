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
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $query->latest()->paginate($this->getPerPageLimit());
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
