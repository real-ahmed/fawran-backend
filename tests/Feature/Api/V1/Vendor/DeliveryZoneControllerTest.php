<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Geo\DeliveryZone;
use App\Models\Geo\VendorDeliveryZone;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeliveryZoneControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private DeliveryZone $masterZone;

    private VendorDeliveryZone $vendorZone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        LocalAccount::create([
            'user_id' => $this->owner->id,
            'password' => Hash::make('password'),
        ]);

        $this->vendor = Vendor::factory()->restaurant()->create([
            'owner_id' => $this->owner->id,
        ]);

        $this->setupVendorPermissions();

        $this->masterZone = DeliveryZone::create([
            'name' => ['en' => 'Downtown', 'ar' => 'وسط المدينة'],
            'polygon' => DB::raw("ST_GeomFromText('POLYGON((0 0, 0 10, 10 10, 10 0, 0 0))')"),
            'is_active' => true,
        ]);

        $this->vendorZone = VendorDeliveryZone::create([
            'vendor_id' => $this->vendor->id,
            'delivery_zone_id' => $this->masterZone->id,
            'min_order_amount' => 50.00,
            'estimated_delivery_time' => 30,
        ]);

        $this->token = auth('api')->login($this->owner);
    }

    private function setupVendorPermissions(): void
    {
        foreach (VendorPermission::values() as $permissionName) {
            Permission::findOrCreate($permissionName, 'api');
        }

        $role = Role::create([
            'name' => 'owner',
            'guard_name' => 'api',
            'vendor_id' => $this->vendor->id,
        ]);
        $role->givePermissionTo(VendorPermission::values());

        setPermissionsTeamId($this->vendor->id);
        $this->owner->assignRole($role);
    }

    private function vendorHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'X-VENDOR-ID' => (string) $this->vendor->id,
        ];
    }

    public function test_can_list_delivery_zones(): void
    {
        $response = $this->getJson('/api/v1/vendor/delivery-zones', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.delivery_zone_id', $this->masterZone->id);
    }

    public function test_can_add_delivery_zone(): void
    {
        $newMasterZone = DeliveryZone::create([
            'name' => ['en' => 'Uptown', 'ar' => 'شمال المدينة'],
            'polygon' => DB::raw("ST_GeomFromText('POLYGON((0 0, 0 10, 10 10, 10 0, 0 0))')"),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/vendor/delivery-zones', [
            'delivery_zone_id' => $newMasterZone->id,
            'min_order_amount' => 100.00,
            'estimated_delivery_time' => 45,
        ], $this->vendorHeaders());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.delivery_zone_id', $newMasterZone->id)
            ->assertJsonPath('data.min_order_amount', 100)
            ->assertJsonPath('data.estimated_delivery_time', 45);

        $this->assertDatabaseHas('vendor_delivery_zones', [
            'vendor_id' => $this->vendor->id,
            'delivery_zone_id' => $newMasterZone->id,
        ]);
    }

    public function test_can_update_delivery_zone(): void
    {
        $response = $this->putJson("/api/v1/vendor/delivery-zones/{$this->vendorZone->id}", [
            'min_order_amount' => 60.00,
            'estimated_delivery_time' => 40,
        ], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.min_order_amount', 60)
            ->assertJsonPath('data.estimated_delivery_time', 40);

        $this->assertDatabaseHas('vendor_delivery_zones', [
            'id' => $this->vendorZone->id,
            'min_order_amount' => 60.00,
            'estimated_delivery_time' => 40,
        ]);
    }

    public function test_can_delete_delivery_zone(): void
    {
        $response = $this->deleteJson("/api/v1/vendor/delivery-zones/{$this->vendorZone->id}", [], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('vendor_delivery_zones', [
            'id' => $this->vendorZone->id,
        ]);
    }

    public function test_cannot_update_other_vendor_delivery_zone(): void
    {
        $otherVendor = Vendor::factory()->restaurant()->create();
        $otherVendorZone = VendorDeliveryZone::create([
            'vendor_id' => $otherVendor->id,
            'delivery_zone_id' => $this->masterZone->id,
            'min_order_amount' => 50.00,
            'estimated_delivery_time' => 30,
        ]);

        $response = $this->putJson("/api/v1/vendor/delivery-zones/{$otherVendorZone->id}", [
            'min_order_amount' => 60.00,
        ], $this->vendorHeaders());

        $response->assertStatus(403);
    }
}
