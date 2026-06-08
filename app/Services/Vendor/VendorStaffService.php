<?php

namespace App\Services\Vendor;

use App\Models\Auth\LocalAccount;
use App\Models\User;
use App\Models\Vendor\VendorStaff;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class VendorStaffService
{
    public function listStaff(int $vendorId): LengthAwarePaginator
    {
        return VendorStaff::with(['user.roles' => function ($query) use ($vendorId) {
            $query->where('roles.vendor_id', $vendorId);
        }])
            ->where('vendor_id', $vendorId)
            ->latest('id')
            ->paginate(20);
    }

    public function createStaff(int $vendorId, array $data): VendorStaff
    {
        return DB::transaction(function () use ($vendorId, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'is_active' => true,
            ]);

            LocalAccount::create([
                'user_id' => $user->id,
                'password' => Hash::make($data['password']),
            ]);

            $vendorStaff = VendorStaff::create([
                'user_id' => $user->id,
                'vendor_id' => $vendorId,
            ]);

            if (! empty($data['role_ids'])) {
                $roles = Role::whereIn('id', $data['role_ids'])
                    ->where('vendor_id', $vendorId)
                    ->get();
                $user->assignRole($roles);
            }

            return $vendorStaff->load(['user.roles' => function ($query) use ($vendorId) {
                $query->where('roles.vendor_id', $vendorId);
            }]);
        });
    }

    public function updateStaff(VendorStaff $vendorStaff, int $vendorId, array $data): VendorStaff
    {
        $this->ensureEditableStaffBelongsToVendor($vendorStaff, $vendorId, 'Cannot modify the vendor owner through staff management.');

        return DB::transaction(function () use ($vendorStaff, $data) {
            $user = $vendorStaff->user;

            $userData = array_intersect_key($data, array_flip(['name', 'email', 'phone']));

            if (! empty($userData)) {
                $user->update($userData);
            }

            if (! empty($data['password'])) {
                $localAccount = $user->localAccount;
                if ($localAccount) {
                    $localAccount->update(['password' => Hash::make($data['password'])]);
                } else {
                    LocalAccount::create([
                        'user_id' => $user->id,
                        'password' => Hash::make($data['password']),
                    ]);
                }
            }

            if (isset($data['role_ids'])) {
                $roles = Role::whereIn('id', $data['role_ids'])
                    ->where('vendor_id', $vendorStaff->vendor_id)
                    ->get();

                $existingVendorRoles = $user->roles()->where('roles.vendor_id', $vendorStaff->vendor_id)->get();
                foreach ($existingVendorRoles as $role) {
                    $user->removeRole($role);
                }

                $user->assignRole($roles);
            }

            return $vendorStaff->fresh(['user.roles' => function ($query) use ($vendorStaff) {
                $query->where('roles.vendor_id', $vendorStaff->vendor_id);
            }]);
        });
    }

    public function deleteStaff(VendorStaff $vendorStaff, int $vendorId): void
    {
        $this->ensureEditableStaffBelongsToVendor($vendorStaff, $vendorId, 'Cannot delete the vendor owner.');

        DB::transaction(function () use ($vendorStaff) {
            $user = $vendorStaff->user;

            $existingVendorRoles = $user->roles()->where('roles.vendor_id', $vendorStaff->vendor_id)->get();
            foreach ($existingVendorRoles as $role) {
                $user->removeRole($role);
            }

            $vendorStaff->delete();

        });
    }

    public function getStaff(VendorStaff $vendorStaff, int $vendorId): VendorStaff
    {
        $this->ensureBelongsToVendor($vendorStaff, $vendorId);

        return $vendorStaff->load(['user.roles' => function ($query) use ($vendorStaff) {
            $query->where('roles.vendor_id', $vendorStaff->vendor_id);
        }]);
    }

    private function ensureEditableStaffBelongsToVendor(VendorStaff $vendorStaff, int $vendorId, string $ownerMessage): void
    {
        $this->ensureBelongsToVendor($vendorStaff, $vendorId);

        abort_if((int) $vendorStaff->vendor->owner_id === (int) $vendorStaff->user_id, 403, $ownerMessage);
    }

    private function ensureBelongsToVendor(VendorStaff $vendorStaff, int $vendorId): void
    {
        abort_unless((int) $vendorStaff->vendor_id === $vendorId, 403, 'Unauthorized action.');
    }
}
