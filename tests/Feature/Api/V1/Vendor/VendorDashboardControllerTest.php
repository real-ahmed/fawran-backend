<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Order\Order;
use App\Models\Order\SubOrder;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

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

    public function test_vendor_can_view_dashboard_metrics(): void
    {
        $response = $this->getJson('/api/v1/vendor/dashboard/metrics', $this->vendorHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'orders' => ['today', 'this_week', 'this_month', 'pending', 'preparing'],
                    'revenue' => ['today', 'this_week', 'this_month'],
                    'commissions' => ['this_month'],
                    'rating' => ['average', 'total_reviews'],
                ],
                'errors',
            ]);
    }

    public function test_dashboard_metrics_reflect_actual_data(): void
    {
        // Create some sub-orders for this vendor
        SubOrder::insert([
            [
                'order_id' => $this->createOrder(),
                'vendor_id' => $this->vendor->id,
                'sub_total' => 50.00,
                'status' => 'pending',
                'created_at' => now(),
            ],
            [
                'order_id' => $this->createOrder(),
                'vendor_id' => $this->vendor->id,
                'sub_total' => 75.50,
                'status' => 'preparing',
                'created_at' => now(),
            ],
        ]);

        $response = $this->getJson('/api/v1/vendor/dashboard/metrics', $this->vendorHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('data.orders.today', 2)
            ->assertJsonPath('data.orders.pending', 1)
            ->assertJsonPath('data.orders.preparing', 1)
            ->assertJsonPath('data.revenue.today', 125.50);
    }

    public function test_unauthenticated_user_cannot_view_dashboard(): void
    {
        auth('api')->logout();

        $response = $this->getJson('/api/v1/vendor/dashboard/metrics');

        $response->assertUnauthorized();
    }

    /**
     * Helper to create a minimal order for testing.
     */
    private function createOrder(): int
    {
        return Order::create([
            'order_type' => 'delivery',
            'total_products' => 100,
            'status' => 'pending',
        ])->id;
    }
}
