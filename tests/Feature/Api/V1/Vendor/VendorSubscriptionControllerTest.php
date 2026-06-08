<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Platform\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorSubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private SubscriptionPlan $plan;

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

        $this->plan = SubscriptionPlan::create([
            'name' => ['en' => 'Pro Plan'],
            'monthly_price' => 99.99,
            'commission_percentage' => 5.0,
            'features' => ['feature 1'],
            'is_active' => true,
        ]);

        $this->setupVendorPermissions();

        $this->token = auth('api')->login($this->owner);
    }

    private function setupVendorPermissions(): void
    {
        foreach (VendorPermission::values() as $permissionName) {
            Permission::findOrCreate($permissionName, 'api');
        }

        $ownerRole = Role::create([
            'name' => 'owner',
            'guard_name' => 'api',
            'vendor_id' => $this->vendor->id,
        ]);
        $ownerRole->givePermissionTo(VendorPermission::values());

        setPermissionsTeamId($this->vendor->id);
        $this->owner->assignRole($ownerRole);
    }

    private function vendorHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'X-VENDOR-ID' => (string) $this->vendor->id,
        ];
    }

    public function test_can_view_current_active_subscription(): void
    {
        VendorSubscription::create([
            'vendor_id' => $this->vendor->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/vendor/subscriptions/current', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.plan.id', $this->plan->id);
    }

    public function test_returns_null_if_no_active_subscription(): void
    {
        $response = $this->getJson('/api/v1/vendor/subscriptions/current', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null);
    }

    public function test_can_list_available_subscription_plans(): void
    {
        $response = $this->getJson('/api/v1/vendor/subscriptions/plans', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->plan->id);
    }
}
