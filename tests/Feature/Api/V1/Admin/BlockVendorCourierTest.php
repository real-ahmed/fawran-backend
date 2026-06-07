<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AdminPermission;
use App\Enums\VendorType;
use App\Models\Admin;
use App\Models\Courier\Courier;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BlockVendorCourierTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        setPermissionsTeamId(0);

        $this->admin = Admin::factory()->create();

        foreach ([
            AdminPermission::VIEW_COURIERS,
            AdminPermission::UPDATE_COURIERS,
            AdminPermission::VIEW_VENDORS,
            AdminPermission::UPDATE_VENDORS,
        ] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'api_admin',
            ]);
        }

        $role = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'api_admin',
            'vendor_id' => 0,
        ]);
        $role->givePermissionTo(Permission::all());
        $this->admin->assignRole($role);
    }

    public function test_admin_can_block_and_unblock_vendor(): void
    {
        $vendor = $this->createVendor();

        $this->actingAs($this->admin, 'api_admin')
            ->putJson("/api/v1/admin/vendors/{$vendor->id}/block", [
                'is_blocked' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'is_active' => false,
        ]);

        $this->actingAs($this->admin, 'api_admin')
            ->putJson("/api/v1/admin/vendors/{$vendor->id}/block", [
                'is_blocked' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', true);
    }

    public function test_blocked_vendor_cannot_use_vendor_routes(): void
    {
        $vendor = $this->createVendor([
            'is_active' => false,
        ]);

        $this->actingAs($vendor->owner, 'api')
            ->withHeader('X-VENDOR-ID', (string) $vendor->id)
            ->getJson('/api/v1/vendor/me')
            ->assertForbidden()
            ->assertJsonPath('message', __('messages.vendor_blocked'));
    }

    public function test_admin_can_block_and_unblock_courier(): void
    {
        $courier = $this->createCourier([
            'is_online' => true,
        ]);

        $this->actingAs($this->admin, 'api_admin')
            ->putJson("/api/v1/admin/couriers/{$courier->id}/block", [
                'is_blocked' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_blocked', true)
            ->assertJsonPath('data.is_online', false);

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'is_blocked' => true,
            'is_online' => false,
        ]);

        $this->actingAs($this->admin, 'api_admin')
            ->putJson("/api/v1/admin/couriers/{$courier->id}/block", [
                'is_blocked' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_blocked', false);
    }

    public function test_admin_can_filter_blocked_couriers(): void
    {
        $blockedCourier = $this->createCourier([
            'is_blocked' => true,
        ]);
        $availableCourier = $this->createCourier();

        $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/couriers?is_blocked=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $blockedCourier->id)
            ->assertJsonMissing(['id' => $availableCourier->id]);
    }

    public function test_blocked_courier_cannot_use_courier_routes(): void
    {
        $courier = $this->createCourier([
            'is_blocked' => true,
        ]);

        $this->actingAs($courier->user, 'api')
            ->getJson('/api/v1/courier/me')
            ->assertForbidden()
            ->assertJsonPath('message', __('messages.courier_blocked'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createVendor(array $overrides = []): Vendor
    {
        return Vendor::create(array_merge([
            'owner_id' => User::factory()->create()->id,
            'name' => ['en' => 'Test Vendor', 'ar' => 'متجر تجريبي'],
            'type' => VendorType::RESTAURANT,
            'email' => uniqid('vendor').'.test@fawran.test',
            'phone' => '+9665'.fake()->numerify('########'),
            'formatted_address' => 'Riyadh, SA',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createCourier(array $overrides = []): Courier
    {
        return Courier::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'vehicle_type' => 'motorcycle',
            'plate_number' => fake()->bothify('BLK-####'),
            'national_id' => fake()->numerify('##############'),
            'is_online' => false,
            'is_blocked' => false,
        ], $overrides));
    }
}
