<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Payment\PayoutRequest;
use App\Models\Payment\Wallet;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorFinanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private Wallet $wallet;

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

        $this->wallet = Wallet::create([
            'user_id' => $this->owner->id,
            'balance' => 500.00,
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

    public function test_can_view_wallet_balance(): void
    {
        $response = $this->getJson('/api/v1/vendor/finances/wallet', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.balance', '500.00');
    }

    public function test_can_list_payout_requests(): void
    {
        PayoutRequest::create([
            'user_id' => $this->owner->id,
            'amount' => 100.00,
            'bank_details' => 'Bank A - 123456',
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/v1/vendor/finances/payout-requests', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.amount', '100.00');
    }

    public function test_can_create_payout_request_with_sufficient_funds(): void
    {
        $payload = [
            'amount' => 200,
            'bank_details' => 'My Bank - Account 999',
        ];

        $response = $this->postJson('/api/v1/vendor/finances/payout-requests', $payload, $this->vendorHeaders());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.amount', '200.00');

        // Wallet balance should be deducted
        $this->assertEquals(300.00, $this->wallet->fresh()->balance);
        $this->assertDatabaseHas('payout_requests', [
            'user_id' => $this->owner->id,
            'amount' => 200.00,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_create_payout_request_with_insufficient_funds(): void
    {
        $payload = [
            'amount' => 1000,
            'bank_details' => 'My Bank - Account 999',
        ];

        $response = $this->postJson('/api/v1/vendor/finances/payout-requests', $payload, $this->vendorHeaders());

        $response->assertStatus(400)
            ->assertJsonPath('message', __('messages.insufficient_funds'));

        // Wallet balance should remain the same
        $this->assertEquals(500.00, $this->wallet->fresh()->balance);
    }
}
