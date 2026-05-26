<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sync permissions first
        $this->call(AdminPermissionSyncSeeder::class);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        setPermissionsTeamId(0);

        // Create Super Admin Role
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api_admin', 'vendor_id' => 0]);
        $role->syncPermissions(Permission::where('guard_name', 'api_admin')->get());

        // Create Default Admin and Assign Role
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@fawran.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        setPermissionsTeamId(0);
        $admin->assignRole($role);
    }
}
