<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AdminPermission;
use App\Enums\VendorType;
use App\Models\Admin;
use App\Models\Geo\DeliveryZone;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable permission caching during tests
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Admin setup
        $this->admin = Admin::factory()->create();

        // Seed permissions
        setPermissionsTeamId(0);
        foreach (AdminPermission::values() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api_admin']);
        }
    }

    public function test_can_list_vendors_with_permission()
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api_admin', 'vendor_id' => 0]);
        $this->admin->assignRole($role);

        setPermissionsTeamId(0);
        $this->admin->givePermissionTo(AdminPermission::VIEW_VENDORS->value);

        $user = User::factory()->create();

        Vendor::create([
            'owner_id' => $user->id,
            'name' => ['en' => 'Test Vendor', 'ar' => 'متجر تجريبي'],
            'type' => VendorType::RESTAURANT,
            'email' => 'vendor@fawran.test',
            'phone' => '+966500000000',
            'formatted_address' => 'Riyadh, SA',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'api_admin')->getJson('/api/v1/admin/vendors');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'type']]]);
    }

    public function test_cannot_list_vendors_without_permission()
    {
        $response = $this->actingAs($this->admin, 'api_admin')->getJson('/api/v1/admin/vendors');

        $response->assertStatus(403);
    }

    public function test_can_create_vendor_with_permission()
    {
        setPermissionsTeamId(0);
        $this->admin->givePermissionTo(AdminPermission::CREATE_VENDORS->value);

        $user = User::factory()->create();

        $payload = [
            'owner_id' => $user->id,
            'name' => [
                'en' => 'New Store',
                'ar' => 'متجر جديد',
            ],
            'type' => VendorType::GROCERY->value,
            'email' => 'new@fawran.test',
            'phone' => '+966511111111',
            'formatted_address' => 'Jeddah, SA',
            'latitude' => 25.0,
            'longitude' => 45.0,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin, 'api_admin')->postJson('/api/v1/admin/vendors', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name.en', 'New Store');

        $this->assertDatabaseHas('vendors', [
            'type' => VendorType::GROCERY->value,
            'email' => 'new@fawran.test',
        ]);
    }

    public function test_can_update_vendor()
    {
        setPermissionsTeamId(0);
        $this->admin->givePermissionTo(AdminPermission::UPDATE_VENDORS->value);
        $deliveryZone = $this->createDeliveryZone();
        $this->admin->deliveryZones()->attach($deliveryZone);

        $user = User::factory()->create();

        $vendor = Vendor::create([
            'owner_id' => $user->id,
            'name' => ['en' => 'Test Vendor', 'ar' => 'متجر تجريبي'],
            'type' => VendorType::RESTAURANT,
            'email' => 'vendor2@fawran.test',
            'phone' => '+966500000002',
            'formatted_address' => 'Riyadh, SA',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'is_active' => true,
        ]);
        $vendor->deliveryZones()->create([
            'delivery_zone_id' => $deliveryZone->id,
            'min_order_amount' => 0,
            'estimated_delivery_time' => 30,
        ]);

        $payload = [
            'is_active' => false,
            'name' => ['en' => 'Updated Name', 'ar' => 'تحديث'],
        ];

        $response = $this->actingAs($this->admin, 'api_admin')->putJson("/api/v1/admin/vendors/{$vendor->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', false);
    }

    public function test_can_delete_vendor()
    {
        setPermissionsTeamId(0);
        $this->admin->givePermissionTo(AdminPermission::DELETE_VENDORS->value);
        $deliveryZone = $this->createDeliveryZone();
        $this->admin->deliveryZones()->attach($deliveryZone);

        $user = User::factory()->create();

        $vendor = Vendor::create([
            'owner_id' => $user->id,
            'name' => ['en' => 'Test Vendor', 'ar' => 'متجر تجريبي'],
            'type' => VendorType::RESTAURANT,
            'email' => 'vendor3@fawran.test',
            'phone' => '+966500000003',
            'formatted_address' => 'Riyadh, SA',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'is_active' => true,
        ]);
        $vendor->deliveryZones()->create([
            'delivery_zone_id' => $deliveryZone->id,
            'min_order_amount' => 0,
            'estimated_delivery_time' => 30,
        ]);

        $response = $this->actingAs($this->admin, 'api_admin')->deleteJson("/api/v1/admin/vendors/{$vendor->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    private function createDeliveryZone(): DeliveryZone
    {
        $deliveryZone = new DeliveryZone;
        $deliveryZone->name = ['en' => 'Test Zone', 'ar' => 'منطقة اختبار'];
        $deliveryZone->polygon = DB::raw("ST_GeomFromText('POLYGON((46.671 24.711,46.678 24.715,46.685 24.708,46.671 24.711))')");
        $deliveryZone->is_active = true;
        $deliveryZone->save();

        return $deliveryZone;
    }
}
