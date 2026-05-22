<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AdminSeeder::class);
    }

    protected function authenticateAdmin(): array
    {
        $admin = Admin::where('email', 'admin@fawran.test')->first();
        $token = auth('api_admin')->login($admin);
        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_admin_can_view_roles(): void
    {
        $headers = $this->authenticateAdmin();

        $response = $this->getJson('/api/v1/admin/roles', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'permissions'
                    ]
                ],
            ]);
    }

    public function test_admin_can_create_role(): void
    {
        $headers = $this->authenticateAdmin();

        $payload = [
            'name' => 'Manager',
            'permissions' => ['view roles', 'create roles'],
        ];

        $response = $this->postJson('/api/v1/admin/roles', $payload, $headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', [
            'name' => 'Manager',
            'guard_name' => 'api_admin'
        ]);
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $headers = $this->authenticateAdmin();
        $role = Role::where('name', 'Super Admin')->first();

        $response = $this->deleteJson('/api/v1/admin/roles/' . $role->id, [], $headers);

        $response->assertStatus(422); // ValidationException converted to 422
    }
}
