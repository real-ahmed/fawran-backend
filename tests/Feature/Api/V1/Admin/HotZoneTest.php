<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AdminPermission;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class HotZoneTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        setPermissionsTeamId(0);

        $this->admin = Admin::factory()->create();
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'api_admin']);
        Permission::create(['name' => AdminPermission::VIEW_HOT_ZONES->value, 'guard_name' => 'api_admin']);
        $role->givePermissionTo(Permission::all());
        $this->admin->assignRole($role);
    }

    public function test_admin_can_list_hot_zones(): void
    {
        $response = $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/hot-zones');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'errors',
                'meta' => [
                    'per_page',
                    'next_cursor',
                    'previous_cursor',
                ],
            ]);
    }
}
