<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Catalog\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminBrandControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        setPermissionsTeamId(0);

        $this->admin = Admin::factory()->create();
        $role = Role::create(['name' => 'super-admin', 'guard_name' => 'api_admin']);
        Permission::create(['name' => 'view brands', 'guard_name' => 'api_admin']);
        Permission::create(['name' => 'create brands', 'guard_name' => 'api_admin']);
        Permission::create(['name' => 'update brands', 'guard_name' => 'api_admin']);
        Permission::create(['name' => 'delete brands', 'guard_name' => 'api_admin']);

        $role->givePermissionTo(Permission::all());
        $this->admin->assignRole($role);
    }

    public function test_it_can_list_brands(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        // Brand factory isn't guaranteed to exist, so we manually create one
        Brand::create(['name' => ['en' => 'Test Brand'], 'is_active' => true]);

        $response = $this->getJson('/api/v1/admin/brands');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name.en', 'Test Brand');
    }

    public function test_it_can_create_a_brand(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        $data = [
            'name' => [
                'en' => 'New Brand En',
                'ar' => 'New Brand Ar',
            ],
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/admin/brands', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name.en', 'New Brand En');

        $this->assertDatabaseHas('brands', [
            'name->en' => 'New Brand En',
        ]);
    }

    public function test_it_can_show_a_brand(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        $brand = Brand::create(['name' => ['en' => 'Test Brand'], 'is_active' => true]);

        $response = $this->getJson("/api/v1/admin/brands/{$brand->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name.en', 'Test Brand');
    }

    public function test_it_can_update_a_brand(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        $brand = Brand::create(['name' => ['en' => 'Old Brand'], 'is_active' => true]);

        $data = [
            'name' => [
                'en' => 'Updated Brand',
            ],
        ];

        $response = $this->putJson("/api/v1/admin/brands/{$brand->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name.en', 'Updated Brand');

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name->en' => 'Updated Brand',
        ]);
    }

    public function test_it_can_delete_a_brand(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        $brand = Brand::create(['name' => ['en' => 'To Delete'], 'is_active' => true]);

        $response = $this->deleteJson("/api/v1/admin/brands/{$brand->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('brands', [
            'id' => $brand->id,
        ]);
    }
}
