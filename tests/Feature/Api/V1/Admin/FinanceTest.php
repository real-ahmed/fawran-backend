<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\Platform\PlatformWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        setPermissionsTeamId(0);

        $this->admin = Admin::factory()->create();
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'api_admin']);
        Permission::create(['name' => AdminPermission::VIEW_FINANCES->value, 'guard_name' => 'api_admin']);
        $role->givePermissionTo(Permission::all());
        $this->admin->assignRole($role);

        PlatformWallet::create(['total_revenue' => 500, 'current_balance' => 250]);
    }

    public function test_admin_can_view_finance_overview(): void
    {
        $response = $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/finances/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'platform',
                    'commissions',
                ],
            ]);
    }

    public function test_delegate_admin_sees_scoped_finances(): void
    {
        Schema::disableForeignKeyConstraints();

        $zone1Id = DB::table('delivery_zones')->insertGetId(['name' => '{"en": "Zone 1"}', 'is_active' => 1, 'polygon' => DB::raw("ST_GeomFromText('POLYGON((0 0, 0 1, 1 1, 1 0, 0 0))')")]);
        $zone2Id = DB::table('delivery_zones')->insertGetId(['name' => '{"en": "Zone 2"}', 'is_active' => 1, 'polygon' => DB::raw("ST_GeomFromText('POLYGON((10 10, 10 11, 11 11, 11 10, 10 10))')")]);

        $delegate = Admin::factory()->create();
        $role = Role::create(['name' => 'Delegate', 'guard_name' => 'api_admin']);
        $role->givePermissionTo(AdminPermission::VIEW_FINANCES->value);
        $delegate->assignRole($role);
        $delegate->deliveryZones()->attach($zone1Id);

        $user1Id = DB::table('users')->insertGetId(['name' => 'User 1', 'email' => 'user1@test.com', 'phone' => '123456789']);
        $vendor1Id = DB::table('vendors')->insertGetId(['owner_id' => $user1Id, 'name' => '{"en": "V1"}', 'email' => 'v1@test.com', 'phone' => '111', 'latitude' => 0, 'longitude' => 0, 'formatted_address' => 'X']);
        DB::table('vendor_delivery_zones')->insert(['vendor_id' => $vendor1Id, 'delivery_zone_id' => $zone1Id, 'min_order_amount' => 0, 'estimated_delivery_time' => 30]);
        DB::table('payout_requests')->insert(['user_id' => $user1Id, 'amount' => 100, 'status' => 'pending', 'bank_details' => 'x']);

        $user2Id = DB::table('users')->insertGetId(['name' => 'User 2', 'email' => 'user2@test.com', 'phone' => '987654321']);
        $vendor2Id = DB::table('vendors')->insertGetId(['owner_id' => $user2Id, 'name' => '{"en": "V2"}', 'email' => 'v2@test.com', 'phone' => '222', 'latitude' => 0, 'longitude' => 0, 'formatted_address' => 'X']);
        DB::table('vendor_delivery_zones')->insert(['vendor_id' => $vendor2Id, 'delivery_zone_id' => $zone2Id, 'min_order_amount' => 0, 'estimated_delivery_time' => 30]);
        DB::table('payout_requests')->insert(['user_id' => $user2Id, 'amount' => 200, 'status' => 'pending', 'bank_details' => 'y']);

        $addressId = DB::table('user_addresses')->insertGetId(['user_id' => 1, 'latitude' => 0, 'longitude' => 0, 'formatted_address' => 'Home', 'phone' => '123', 'building_number' => '1']);

        $order1Id = DB::table('orders')->insertGetId(['total_products' => 1, 'order_type' => 'normal', 'status' => 'pending']);
        DB::table('order_deliveries')->insert(['order_id' => $order1Id, 'delivery_zone_id' => $zone1Id, 'address_id' => $addressId, 'total_delivery_fee' => 0]);
        DB::table('order_commissions')->insert(['order_id' => $order1Id, 'vendor_id' => $vendor1Id, 'net_platform_profit' => 10, 'app_delivery_share' => 5, 'vendorcommission_amount' => 5, 'vendorcommission_percentage' => 10]);

        $order2Id = DB::table('orders')->insertGetId(['total_products' => 1, 'order_type' => 'normal', 'status' => 'pending']);
        DB::table('order_deliveries')->insert(['order_id' => $order2Id, 'delivery_zone_id' => $zone2Id, 'address_id' => $addressId, 'total_delivery_fee' => 0]);
        DB::table('order_commissions')->insert(['order_id' => $order2Id, 'vendor_id' => $vendor2Id, 'net_platform_profit' => 20, 'app_delivery_share' => 10, 'vendorcommission_amount' => 10, 'vendorcommission_percentage' => 10]);

        $courierUser1Id = DB::table('users')->insertGetId(['name' => 'Courier 1', 'email' => 'courier1@test.com', 'phone' => '111111111']);
        $courier1Id = DB::table('couriers')->insertGetId(['user_id' => $courierUser1Id, 'national_id' => '29001010101010', 'vehicle_type' => 'motorcycle', 'plate_number' => 'C-1']);
        DB::table('courier_locations')->insert(['courier_id' => $courier1Id, 'latitude' => 0.5, 'longitude' => 0.5, 'located_at' => now()]);
        DB::table('courier_cash_collections')->insert(['courier_id' => $courier1Id, 'amount_collected' => 70, 'courier_fee_share' => 20, 'amount_owed_to_platform' => 50, 'is_settled' => 0, 'source_type' => 'order', 'source_id' => 1, 'collected_at' => now()]);

        $courierUser2Id = DB::table('users')->insertGetId(['name' => 'Courier 2', 'email' => 'courier2@test.com', 'phone' => '222222222']);
        $courier2Id = DB::table('couriers')->insertGetId(['user_id' => $courierUser2Id, 'national_id' => '29001010101011', 'vehicle_type' => 'motorcycle', 'plate_number' => 'C-2']);
        DB::table('courier_locations')->insert(['courier_id' => $courier2Id, 'latitude' => 10.5, 'longitude' => 10.5, 'located_at' => now()]);
        DB::table('courier_cash_collections')->insert(['courier_id' => $courier2Id, 'amount_collected' => 120, 'courier_fee_share' => 20, 'amount_owed_to_platform' => 100, 'is_settled' => 0, 'source_type' => 'order', 'source_id' => 2, 'collected_at' => now()]);

        DB::table('settlements')->insert(['settlement_type' => 'courier', 'target_id' => $courier1Id, 'period_start' => now()->subWeek()->toDateString(), 'period_end' => now()->toDateString(), 'total_gross' => 100, 'total_deductions' => 0, 'total_net_exchange' => 100, 'status' => 'pending']);
        DB::table('settlements')->insert(['settlement_type' => 'courier', 'target_id' => $courier2Id, 'period_start' => now()->subWeek()->toDateString(), 'period_end' => now()->toDateString(), 'total_gross' => 200, 'total_deductions' => 0, 'total_net_exchange' => 200, 'status' => 'pending']);

        Schema::enableForeignKeyConstraints();

        $response = $this->actingAs($delegate, 'api_admin')
            ->getJson('/api/v1/admin/finances/overview');

        $response->assertStatus(200)
            ->assertJsonPath('data.commissions.total_platform_profit', '10.00')
            ->assertJsonPath('data.cash.unsettled_total', '50.00')
            ->assertJsonPath('data.payouts.pending_total', '100.00')
            ->assertJsonPath('data.settlements.pending_total', '100.00');
    }
}
