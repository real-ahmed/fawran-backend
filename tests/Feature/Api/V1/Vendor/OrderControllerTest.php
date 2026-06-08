<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\SubOrderStatus;
use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Order\Order;
use App\Models\Order\OrderCustomer;
use App\Models\Order\SubOrder;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private Order $order;

    private SubOrder $subOrder;

    private User $customer;

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

        $this->order = Order::create([
            'order_type' => 'delivery',
            'total_products' => 15.50,
            'status' => 'pending',
        ]);

        $this->customer = User::factory()->create();

        OrderCustomer::create([
            'order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
        ]);

        $this->subOrder = SubOrder::create([
            'order_id' => $this->order->id,
            'vendor_id' => $this->vendor->id,
            'sub_total' => 15.50,
            'status' => SubOrderStatus::Pending,
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

    public function test_can_list_orders(): void
    {
        $response = $this->getJson('/api/v1/vendor/orders', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', SubOrderStatus::Pending->value);
    }

    public function test_can_get_status_counts(): void
    {
        $response = $this->getJson('/api/v1/vendor/orders/status-counts', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.'.SubOrderStatus::Pending->value, 1);
    }

    public function test_can_show_order(): void
    {
        $response = $this->getJson("/api/v1/vendor/orders/{$this->subOrder->id}", $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.customer.name', $this->customer->name);
    }

    public function test_can_update_order_status(): void
    {
        $response = $this->putJson("/api/v1/vendor/orders/{$this->subOrder->id}/status", [
            'status' => SubOrderStatus::Preparing->value,
        ], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', SubOrderStatus::Preparing->value);

        $this->assertDatabaseHas('sub_orders', [
            'id' => $this->subOrder->id,
            'status' => SubOrderStatus::Preparing->value,
        ]);
    }

    public function test_cannot_update_invalid_status_transition(): void
    {
        $response = $this->putJson("/api/v1/vendor/orders/{$this->subOrder->id}/status", [
            'status' => SubOrderStatus::ReadyForPickup->value, // Cannot go straight from pending to ready_for_pickup
        ], $this->vendorHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
