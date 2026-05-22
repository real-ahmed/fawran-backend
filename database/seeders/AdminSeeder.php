<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Enums\AdminPermission;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        setPermissionsTeamId(0);

        // Seed all permissions from Enum
        foreach (AdminPermission::values() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api_admin']);
        }

        // Create Super Admin Role
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api_admin', 'vendor_id' => 0]);

        // Create Default Admin and Assign Role
        $admin = \App\Models\Admin::firstOrCreate(
            ['email' => 'admin@fawran.test'],
            [
                'name' => 'Super Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        setPermissionsTeamId(0);
        $admin->assignRole($role);
    }
}
