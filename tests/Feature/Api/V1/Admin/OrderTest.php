<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AdminPermission;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\SubOrderStatus;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Models\Admin;
use App\Models\Order\Order;
use App\Models\Order\SubOrder;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OrderTest extends TestCase
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
        Permission::create(['name' => AdminPermission::VIEW_ORDERS->value, 'guard_name' => 'api_admin']);
        $role->givePermissionTo(Permission::all());
        $this->admin->assignRole($role);
    }

    public function test_admin_can_list_orders(): void
    {
        $vendor = Vendor::create([
            'owner_id' => User::factory()->create()->id,
            'name' => ['en' => 'Fresh Market'],
            'type' => VendorType::GROCERY->value,
            'email' => 'fresh-market@example.test',
            'phone' => '0500000001',
            'latitude' => 24.71360000,
            'longitude' => 46.67530000,
            'formatted_address' => 'Riyadh',
            'status' => VendorStatus::ONLINE->value,
        ]);

        $order = Order::create([
            'order_type' => OrderType::Delivery->value,
            'total_products' => 15.50,
            'status' => OrderStatus::Pending->value,
        ]);

        SubOrder::create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'sub_total' => 15.50,
            'status' => SubOrderStatus::Pending->value,
        ]);

        $response = $this->actingAs($this->admin, 'api_admin')
            ->getJson('/api/v1/admin/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ])
            ->assertJsonPath('data.0.sub_orders.0.vendor_id', $vendor->id)
            ->assertJsonPath('data.0.sub_orders.0.vendor_name', 'Fresh Market');
    }
}
