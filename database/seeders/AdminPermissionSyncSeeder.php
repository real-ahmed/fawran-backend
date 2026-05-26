<?php

namespace Database\Seeders;

use App\Enums\AdminPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AdminPermissionSyncSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        setPermissionsTeamId(0);

        $enumPermissions = AdminPermission::values();

        // Seed all permissions from Enum
        foreach ($enumPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api_admin']);
        }

        // Delete any permissions in database that are no longer in the Enum
        Permission::where('guard_name', 'api_admin')
            ->whereNotIn('name', $enumPermissions)
            ->delete();
    }
}
