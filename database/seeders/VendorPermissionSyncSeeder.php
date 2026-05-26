<?php

namespace Database\Seeders;

use App\Enums\VendorPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class VendorPermissionSyncSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        setPermissionsTeamId(0);

        $enumPermissions = VendorPermission::values();

        // Seed all permissions from Enum
        foreach ($enumPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api_vendor']);
        }

        // Delete any permissions in database that are no longer in the Enum
        Permission::where('guard_name', 'api_vendor')
            ->whereNotIn('name', $enumPermissions)
            ->delete();
    }
}
