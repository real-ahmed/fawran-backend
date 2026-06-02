<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\Role;
use App\Notifications\Admin\AdminCredentialsGeneratedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Admin setup
        $this->superAdmin = Admin::factory()->create();

        setPermissionsTeamId(0);
        foreach (AdminPermission::values() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api_admin']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => Role::SUPER_ADMIN_NAME, 'guard_name' => 'api_admin']);
        $this->superAdmin->assignRole($superAdminRole);
    }

    public function test_can_list_admins_with_permission()
    {
        setPermissionsTeamId(0);
        $this->superAdmin->givePermissionTo(AdminPermission::VIEW_ADMINS->value);

        Admin::factory()->count(2)->create();

        $response = $this->actingAs($this->superAdmin, 'api_admin')->getJson('/api/v1/admin/admins');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'email', 'is_active', 'roles']]]);
    }

    public function test_can_create_admin()
    {
        Queue::fake();
        setPermissionsTeamId(0);
        $this->superAdmin->givePermissionTo(AdminPermission::CREATE_ADMINS->value);

        $role = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'api_admin']);
        $payload = [
            'name' => 'New Manager',
            'email' => 'manager@fawran.test',
            'is_active' => true,
            'roles' => ['Manager'],
        ];

        $response = $this->actingAs($this->superAdmin, 'api_admin')->postJson('/api/v1/admin/admins', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Manager')
            ->assertJsonPath('data.roles.0.name', 'Manager');

        $this->assertDatabaseHas('admins', [
            'email' => 'manager@fawran.test',
        ]);

        $createdAdmin = Admin::where('email', 'manager@fawran.test')->first();
        Queue::assertPushedOn(
            'notifications',
            SendQueuedNotifications::class,
            fn (SendQueuedNotifications $job): bool => $job->notification instanceof AdminCredentialsGeneratedNotification
                && $job->channels === ['mail']
                && $job->notifiables->first() instanceof Admin
                && $job->notifiables->first()->is($createdAdmin)
        );
    }

    public function test_cannot_create_admin_with_super_admin_role()
    {
        Queue::fake();
        setPermissionsTeamId(0);
        $this->superAdmin->givePermissionTo(AdminPermission::CREATE_ADMINS->value);

        Role::firstOrCreate(['name' => Role::SUPER_ADMIN_NAME, 'guard_name' => 'api_admin']);

        $payload = [
            'name' => 'Second Super Admin',
            'email' => 'second-super-admin@fawran.test',
            'is_active' => true,
            'roles' => [Role::SUPER_ADMIN_NAME],
        ];

        $response = $this->actingAs($this->superAdmin, 'api_admin')->postJson('/api/v1/admin/admins', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles']);

        $this->assertDatabaseMissing('admins', [
            'email' => 'second-super-admin@fawran.test',
        ]);
    }

    public function test_can_update_admin_status_and_roles()
    {
        setPermissionsTeamId(0);
        $this->superAdmin->givePermissionTo(AdminPermission::UPDATE_ADMINS->value);

        $targetAdmin = Admin::factory()->create(['is_active' => true]);
        $role = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'api_admin']);

        $payload = [
            'is_active' => false,
            'roles' => ['Editor'],
        ];

        $response = $this->actingAs($this->superAdmin, 'api_admin')->putJson("/api/v1/admin/admins/{$targetAdmin->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.roles.0.name', 'Editor');
    }

    public function test_cannot_assign_super_admin_role_to_existing_admin()
    {
        setPermissionsTeamId(0);
        $this->superAdmin->givePermissionTo(AdminPermission::UPDATE_ADMINS->value);

        $targetAdmin = Admin::factory()->create();
        Role::firstOrCreate(['name' => Role::SUPER_ADMIN_NAME, 'guard_name' => 'api_admin']);

        $response = $this->actingAs($this->superAdmin, 'api_admin')->putJson("/api/v1/admin/admins/{$targetAdmin->id}", [
            'roles' => [Role::SUPER_ADMIN_NAME],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles']);

        $this->assertFalse($targetAdmin->fresh()->hasRole(Role::SUPER_ADMIN_NAME));
    }

    public function test_cannot_change_super_admin_account_to_another_role()
    {
        setPermissionsTeamId(0);
        $this->superAdmin->givePermissionTo(AdminPermission::UPDATE_ADMINS->value);

        $superAdminRole = Role::firstOrCreate(['name' => Role::SUPER_ADMIN_NAME, 'guard_name' => 'api_admin']);
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'api_admin']);
        $protectedAdmin = Admin::factory()->create();
        $protectedAdmin->assignRole($superAdminRole);

        $response = $this->actingAs($this->superAdmin, 'api_admin')->putJson("/api/v1/admin/admins/{$protectedAdmin->id}", [
            'roles' => ['Editor'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles']);

        $protectedAdmin->refresh();

        $this->assertTrue($protectedAdmin->hasRole(Role::SUPER_ADMIN_NAME));
        $this->assertFalse($protectedAdmin->hasRole('Editor'));
    }

    public function test_can_delete_admin()
    {
        setPermissionsTeamId(0);
        $this->superAdmin->givePermissionTo(AdminPermission::DELETE_ADMINS->value);

        $targetAdmin = Admin::factory()->create();

        // Ensure we are not testing ID 1 accidentally if factory makes ID 1.
        // Super admin created in setUp is ID 1 usually. targetAdmin is ID 2.

        $response = $this->actingAs($this->superAdmin, 'api_admin')->deleteJson("/api/v1/admin/admins/{$targetAdmin->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('admins', ['id' => $targetAdmin->id]);
    }

    public function test_cannot_delete_super_admin_account()
    {
        setPermissionsTeamId(0);
        $this->superAdmin->givePermissionTo(AdminPermission::DELETE_ADMINS->value);

        $superAdminRole = Role::firstOrCreate(['name' => Role::SUPER_ADMIN_NAME, 'guard_name' => 'api_admin']);
        $protectedAdmin = Admin::factory()->create();
        $protectedAdmin->assignRole($superAdminRole);

        $response = $this->actingAs($this->superAdmin, 'api_admin')->deleteJson("/api/v1/admin/admins/{$protectedAdmin->id}");

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Cannot delete a Super Admin account.');

        $this->assertDatabaseHas('admins', ['id' => $protectedAdmin->id]);
    }
}
