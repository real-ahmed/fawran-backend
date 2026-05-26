<?php

namespace Tests\Feature\Api\V1\Store;

use App\Models\User;
use App\Models\Vendor\Vendor;
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

    protected function authenticateStoreUser(): array
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user' . uniqid() . '@example.com',
            'phone' => '123456' . rand(1000, 9999),
            'password' => bcrypt('password'),
        ]);
        
        $vendor = Vendor::create([
            'owner_id' => $user->id,
            'name' => ['en' => 'Test Store'],
            'description' => ['en' => 'Test Description'],
            'email' => 'vendor' . uniqid() . '@example.com',
            'phone' => '12345678' . rand(1000, 9999),
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'formatted_address' => 'Test Address',
            'type' => 'restaurant',
            'is_active' => true,
        ]);

        $vendorId = $vendor->id;

        // Give user permission to manage roles in this store
        setPermissionsTeamId($vendorId);
        $role = Role::create(['name' => 'Vendor Owner', 'guard_name' => 'api', 'vendor_id' => $vendorId]);
        $permission = Permission::firstOrCreate(['name' => 'manage store roles', 'guard_name' => 'api']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);
        
        $token = auth('api')->login($user);
        return [
            'vendor_id' => $vendorId,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'X-VENDOR-ID' => $vendorId,
            ]
        ];
    }

    public function test_vendor_staff_can_view_their_roles(): void
    {
        $this->withoutExceptionHandling();
        $authData = $this->authenticateStoreUser();
        $vendorId = $authData['vendor_id'];
        $headers = $authData['headers'];

        Role::create(['name' => 'Cashier', 'guard_name' => 'api', 'vendor_id' => $vendorId]);

        $response = $this->getJson('/api/v1/vendor/roles', $headers);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // 'Vendor Owner' + 'Cashier'
    }

    public function test_vendor_staff_cannot_view_other_vendor_roles(): void
    {
        $authData = $this->authenticateStoreUser();
        $vendorId1 = $authData['vendor_id'];
        $headers = $authData['headers'];

        // Create another vendor and a role for it
        $vendor2 = Vendor::create([
            'owner_id' => User::factory()->create()->id,
            'name' => ['en' => 'Test Store 2'],
            'email' => 'vendor2' . uniqid() . '@example.com',
            'phone' => '98765432' . rand(1000, 9999),
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'formatted_address' => 'Test Address',
            'type' => 'restaurant',
            'is_active' => true,
        ]);
        $vendorId2 = $vendor2->id;

        Role::create(['name' => 'Cashier Vendor 2', 'guard_name' => 'api', 'vendor_id' => $vendorId2]);

        $response = $this->getJson('/api/v1/vendor/roles', $headers);

        $response->assertStatus(200);
        $this->assertStringNotContainsString('Cashier Vendor 2', $response->getContent());
    }

    public function test_vendor_staff_can_create_role(): void
    {
        $authData = $this->authenticateStoreUser();
        $vendorId = $authData['vendor_id'];
        $headers = $authData['headers'];

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
