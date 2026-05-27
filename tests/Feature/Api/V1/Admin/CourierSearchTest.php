<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\Courier\Courier;
use App\Models\Geo\DeliveryZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CourierSearchTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private DeliveryZone $deliveryZone;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        setPermissionsTeamId(0);

        $this->admin = Admin::factory()->create();
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'api_admin']);
        Permission::create(['name' => AdminPermission::VIEW_COURIERS->value, 'guard_name' => 'api_admin']);
        $role->givePermissionTo(Permission::all());
        $this->admin->assignRole($role);

        $this->deliveryZone = $this->createDeliveryZone();
    }

    public function test_admin_can_search_couriers_by_user_fields(): void
    {
        $matchingUser = User::factory()->create([
            'name' => 'Ahmed Courier',
            'email' => 'ahmed.courier@example.com',
            'phone' => '0500000001',
        ]);

        $otherUser = User::factory()->create([
            'name' => 'Mona Driver',
            'email' => 'mona.driver@example.com',
            'phone' => '0500000002',
        ]);

        $matchingCourier = $this->createCourier($matchingUser, 'ABC-123');
        $otherCourier = $this->createCourier($otherUser, 'XYZ-999');

        $response = $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/couriers?search=ahmed');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingCourier->id)
            ->assertJsonMissing(['id' => $otherCourier->id]);
    }

    public function test_admin_can_search_couriers_by_plate_number(): void
    {
        $matchingCourier = $this->createCourier(User::factory()->create(), 'SRCH-4422');
        $otherCourier = $this->createCourier(User::factory()->create(), 'MISS-1188');

        $response = $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/couriers?search=4422');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingCourier->id)
            ->assertJsonMissing(['id' => $otherCourier->id]);
    }

    private function createCourier(User $user, string $plateNumber): Courier
    {
        return Courier::create([
            'user_id' => $user->id,
            'delivery_zone_id' => $this->deliveryZone->id,
            'vehicle_type' => 'motorcycle',
            'plate_number' => $plateNumber,
            'is_online' => false,
        ]);
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
