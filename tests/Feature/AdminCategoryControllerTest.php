<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Catalog\Category;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminCategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase, WithFaker;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        setPermissionsTeamId(0);

        $this->admin = Admin::factory()->create();
        $role = Role::create(['name' => 'super-admin', 'guard_name' => 'api_admin']);
        Permission::create(['name' => 'view categories', 'guard_name' => 'api_admin']);
        Permission::create(['name' => 'create categories', 'guard_name' => 'api_admin']);
        Permission::create(['name' => 'update categories', 'guard_name' => 'api_admin']);
        Permission::create(['name' => 'delete categories', 'guard_name' => 'api_admin']);

        $role->givePermissionTo(Permission::all());
        $this->admin->assignRole($role);
    }

    public function test_it_can_list_categories(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        Category::create(['name' => ['en' => 'Test Category'], 'is_active' => true]);

        $response = $this->getJson('/api/v1/admin/categories');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name.en', 'Test Category');
    }

    public function test_it_can_create_a_category_with_hierarchy_and_icon(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        $parentCategory = Category::create(['name' => ['en' => 'Parent'], 'is_active' => true]);

        $data = [
            'name' => [
                'en' => 'Child Category',
            ],
            'is_active' => true,
            'parent_category_id' => $parentCategory->id,
            'icon' => \Illuminate\Http\UploadedFile::fake()->create('icon.svg', 10, 'image/svg+xml'),
        ];

        $response = $this->postJson('/api/v1/admin/categories', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name.en', 'Child Category')
            ->assertJsonPath('data.parent_category_id', $parentCategory->id);

        $this->assertNotNull($response->json('data.icon'));

        $this->assertDatabaseHas('categories', [
            'name->en' => 'Child Category',
        ]);

        $this->assertDatabaseHas('category_hierarchies', [
            'parent_category_id' => $parentCategory->id,
        ]);
    }

    public function test_it_can_show_a_category(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        $category = Category::create(['name' => ['en' => 'Test Category'], 'is_active' => true]);

        $response = $this->getJson("/api/v1/admin/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name.en', 'Test Category');
    }

    public function test_it_can_update_a_category(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        $parentCategory = Category::create(['name' => ['en' => 'Parent'], 'is_active' => true]);
        $category = Category::create(['name' => ['en' => 'Old Category'], 'is_active' => true]);

        $data = [
            'name' => [
                'en' => 'Updated Category',
            ],
            'parent_category_id' => $parentCategory->id,
            'icon' => \Illuminate\Http\UploadedFile::fake()->create('updated_icon.svg', 10, 'image/svg+xml'),
        ];

        $response = $this->putJson("/api/v1/admin/categories/{$category->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name.en', 'Updated Category')
            ->assertJsonPath('data.parent_category_id', $parentCategory->id);
            
        $this->assertNotNull($response->json('data.icon'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name->en' => 'Updated Category',
        ]);
    }

    public function test_it_can_delete_a_category_and_extensions(): void
    {
        $this->actingAs($this->admin, 'api_admin');

        $parentCategory = Category::create(['name' => ['en' => 'Parent'], 'is_active' => true]);
        $category = Category::create(['name' => ['en' => 'To Delete'], 'is_active' => true]);
        $category->hierarchy()->create(['parent_category_id' => $parentCategory->id]);
        $category->icon()->create(['icon_path' => 'icons/fake.svg']);

        $response = $this->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);

        $this->assertDatabaseMissing('category_hierarchies', [
            'child_category_id' => $category->id,
        ]);

        $this->assertDatabaseMissing('category_icons', [
            'category_id' => $category->id,
        ]);
    }
}
