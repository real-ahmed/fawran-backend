<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Inventory\Supplier;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        LocalAccount::create([
            'user_id' => $this->owner->id,
            'password' => Hash::make('password'),
        ]);

        $this->vendor = Vendor::factory()->grocery()->create([
            'owner_id' => $this->owner->id,
        ]);

        $this->setupVendorPermissions();

        $this->supplier = Supplier::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Acme Supplies',
            'phone' => '+1234567890',
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

    public function test_can_list_suppliers(): void
    {
        $response = $this->getJson('/api/v1/vendor/suppliers', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->supplier->id);
    }

    public function test_can_add_supplier(): void
    {
        $response = $this->postJson('/api/v1/vendor/suppliers', [
            'name' => 'Beta Supplies',
            'phone' => '+0987654321',
        ], $this->vendorHeaders());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Beta Supplies');

        $this->assertDatabaseHas('suppliers', [
            'vendor_id' => $this->vendor->id,
            'name' => 'Beta Supplies',
        ]);
    }

    public function test_can_update_supplier(): void
    {
        $response = $this->putJson("/api/v1/vendor/suppliers/{$this->supplier->id}", [
            'name' => 'Acme Global Supplies',
        ], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Acme Global Supplies');

        $this->assertDatabaseHas('suppliers', [
            'id' => $this->supplier->id,
            'name' => 'Acme Global Supplies',
        ]);
    }

    public function test_can_delete_supplier(): void
    {
        $response = $this->deleteJson("/api/v1/vendor/suppliers/{$this->supplier->id}", [], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('suppliers', [
            'id' => $this->supplier->id,
        ]);
    }
}
