<?php

namespace Tests\Feature\Api\V1\Store;

use App\Models\User;
use App\Models\Vendor\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class VendorRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AdminSeeder::class); // To seed initial global setup
    }

    protected function authenticateStoreUser(int $vendorId): array
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user' . uniqid() . '@example.com',
            'phone' => '123456' . rand(1000, 9999),
            'password' => bcrypt('password'),
        ]);
        
        // Give user permission to manage roles in this store
        setPermissionsTeamId($vendorId);
        $role = Role::create(['name' => 'Vendor Owner', 'guard_name' => 'api', 'vendor_id' => $vendorId]);
        $permission = Permission::firstOrCreate(['name' => 'manage store roles', 'guard_name' => 'api']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);
        
        $token = auth('api')->login($user);
        return [
            'Authorization' => 'Bearer ' . $token,
            'X-Store-ID' => $vendorId,
        ];
    }

    public function test_vendor_staff_can_view_their_roles(): void
    {
        $vendorId = 1;
        $headers = $this->authenticateStoreUser($vendorId);

        Role::create(['name' => 'Cashier', 'guard_name' => 'api', 'vendor_id' => $vendorId]);

        $response = $this->getJson('/api/v1/vendor/roles', $headers);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // 'Vendor Owner' + 'Cashier'
    }

    public function test_vendor_staff_cannot_view_other_vendor_roles(): void
    {
        $vendorId1 = 1;
        $vendorId2 = 2;
        
        $headers = $this->authenticateStoreUser($vendorId1);

        Role::create(['name' => 'Cashier Vendor 2', 'guard_name' => 'api', 'vendor_id' => $vendorId2]);

        $response = $this->getJson('/api/v1/vendor/roles', $headers);

        $response->assertStatus(200);
        $this->assertStringNotContainsString('Cashier Vendor 2', $response->getContent());
    }

    public function test_vendor_staff_can_create_role(): void
    {
        $vendorId = 1;
        $headers = $this->authenticateStoreUser($vendorId);

        $payload = [
            'name' => 'Manager',
            'permissions' => ['view store orders', 'manage store products'],
        ];

        $response = $this->postJson('/api/v1/vendor/roles', $payload, $headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', [
            'name' => 'Manager',
            'vendor_id' => $vendorId
        ]);
    }
}
