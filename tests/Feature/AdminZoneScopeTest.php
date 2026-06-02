<?php

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Enums\VendorType;
use App\Models\Admin;
use App\Models\Geo\DeliveryZone;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminZoneScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        setPermissionsTeamId(0);

        Permission::create(['name' => AdminPermission::VIEW_VENDORS->value, 'guard_name' => 'api_admin']);
    }

    public function test_zone_admin_only_sees_vendors_in_assigned_zones(): void
    {
        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Reserved Super Role',
            'guard_name' => 'api_admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::statement('ALTER TABLE roles AUTO_INCREMENT = 2');

        $allowedZone = $this->createDeliveryZone('Allowed Zone', 0);
        $blockedZone = $this->createDeliveryZone('Blocked Zone', 10);

        Admin::factory()->create();
        $admin = Admin::factory()->create();
        $role = Role::create(['name' => 'Zone Manager', 'guard_name' => 'api_admin']);
        $role->givePermissionTo(AdminPermission::VIEW_VENDORS->value);
        $admin->assignRole($role);
        $admin->deliveryZones()->attach($allowedZone);

        $allowedVendor = $this->createVendorInZone($allowedZone, 'allowed-vendor@example.test');
        $blockedVendor = $this->createVendorInZone($blockedZone, 'blocked-vendor@example.test');

        $this->actingAs($admin, 'api_admin')
            ->getJson('/api/v1/admin/vendors')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $allowedVendor->id)
            ->assertJsonMissing(['email' => $blockedVendor->email]);

        $this->actingAs($admin, 'api_admin')
            ->getJson("/api/v1/admin/vendors/{$blockedVendor->id}")
            ->assertNotFound();
    }

    public function test_admin_with_role_id_one_sees_all_zone_data(): void
    {
        $zoneOne = $this->createDeliveryZone('Zone One', 0);
        $zoneTwo = $this->createDeliveryZone('Zone Two', 10);

        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Operations Lead',
            'guard_name' => 'api_admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $role = Role::findOrFail(1);

        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        $vendorOne = $this->createVendorInZone($zoneOne, 'first-vendor@example.test');
        $vendorTwo = $this->createVendorInZone($zoneTwo, 'second-vendor@example.test');

        $this->actingAs($admin, 'api_admin')
            ->getJson('/api/v1/admin/vendors')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $vendorOne->id])
            ->assertJsonFragment(['id' => $vendorTwo->id]);
    }

    private function createDeliveryZone(string $name, int $offset): DeliveryZone
    {
        $next = $offset + 1;

        $deliveryZone = new DeliveryZone;
        $deliveryZone->name = ['en' => $name, 'ar' => $name];
        $deliveryZone->polygon = DB::raw("ST_GeomFromText('POLYGON(({$offset} {$offset}, {$offset} {$next}, {$next} {$next}, {$next} {$offset}, {$offset} {$offset}))')");
        $deliveryZone->is_active = true;
        $deliveryZone->save();

        return $deliveryZone;
    }

    private function createVendorInZone(DeliveryZone $deliveryZone, string $email): Vendor
    {
        $vendor = Vendor::create([
            'owner_id' => User::factory()->create()->id,
            'name' => ['en' => $email, 'ar' => $email],
            'type' => VendorType::GROCERY->value,
            'email' => $email,
            'phone' => '+966'.fake()->unique()->numerify('5########'),
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'formatted_address' => 'Riyadh, SA',
            'is_active' => true,
        ]);

        $vendor->deliveryZones()->create([
            'delivery_zone_id' => $deliveryZone->id,
            'min_order_amount' => 0,
            'estimated_delivery_time' => 30,
        ]);

        return $vendor;
    }
}
