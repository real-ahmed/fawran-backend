<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        setPermissionsTeamId(0);

        $this->admin = Admin::factory()->create();
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'api_admin']);
        Permission::create(['name' => AdminPermission::VIEW_CUSTOMERS->value, 'guard_name' => 'api_admin']);
        Permission::create(['name' => AdminPermission::UPDATE_CUSTOMERS->value, 'guard_name' => 'api_admin']);
        $role->givePermissionTo(Permission::all());
        $this->admin->assignRole($role);

        $this->customer = User::factory()->create(['name' => 'Test Customer', 'is_active' => true]);
    }

    public function test_admin_can_list_customers(): void
    {
        $response = $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/customers');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Test Customer');
    }

    public function test_admin_can_toggle_customer_status(): void
    {
        $response = $this->actingAs($this->admin, 'api_admin')
            ->putJson("/api/v1/admin/customers/{$this->customer->id}/status", [
                'is_active' => false,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'is_active' => false,
        ]);
    }
}
