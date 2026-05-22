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

        // Seed all permissions from Enum
        foreach (AdminPermission::values() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api_admin']);
        }

        // Create Super Admin Role (permissions are granted implicitly via Gate::before in AppServiceProvider)
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api_admin']);

        // Create Default Admin and Assign Role
        $admin = \App\Models\Admin::firstOrCreate(
            ['email' => 'admin@fawran.test'],
            [
                'name' => 'Super Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        $admin->assignRole($role);
    }
}
