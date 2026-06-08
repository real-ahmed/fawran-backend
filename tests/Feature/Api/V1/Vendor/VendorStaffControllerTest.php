<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorStaff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorStaffControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private Role $role;

    private User $staffUser;

    private VendorStaff $vendorStaff;

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

        // Create a custom role for the vendor
        $this->role = Role::create([
            'name' => 'Manager',
            'guard_name' => 'api',
            'vendor_id' => $this->vendor->id,
        ]);

        // Create an existing staff member
        $this->staffUser = User::factory()->create();
        $this->staffUser->assignRole($this->role);

        $this->vendorStaff = VendorStaff::create([
            'user_id' => $this->staffUser->id,
            'vendor_id' => $this->vendor->id,
        ]);

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

    public function test_can_list_staff(): void
    {
        $response = $this->getJson('/api/v1/vendor/staff', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            // It might return 1 (staff) or 2 (staff + owner if owner is in VendorStaff).
            // In our setup we only added the staffUser to VendorStaff.
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->vendorStaff->id)
            ->assertJsonPath('data.0.user.id', $this->staffUser->id)
            ->assertJsonPath('data.0.roles.0.id', $this->role->id);
    }

    public function test_can_show_staff(): void
    {
        $response = $this->getJson("/api/v1/vendor/staff/{$this->vendorStaff->id}", $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->vendorStaff->id)
            ->assertJsonPath('data.user.id', $this->staffUser->id);
    }

    public function test_can_create_staff(): void
    {
        $payload = [
            'name' => 'New Staff',
            'email' => 'staff123@example.com',
            'phone' => '+1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_ids' => [$this->role->id],
        ];

        $response = $this->postJson('/api/v1/vendor/staff', $payload, $this->vendorHeaders());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', 'New Staff')
            ->assertJsonPath('data.roles.0.id', $this->role->id);

        $this->assertDatabaseHas('users', ['email' => 'staff123@example.com']);
        $this->assertDatabaseHas('vendorstaff', ['vendor_id' => $this->vendor->id]);
    }

    public function test_can_update_staff(): void
    {
        $payload = [
            'name' => 'Updated Staff',
        ];

        $response = $this->putJson("/api/v1/vendor/staff/{$this->vendorStaff->id}", $payload, $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', 'Updated Staff');

        $this->assertDatabaseHas('users', [
            'id' => $this->staffUser->id,
            'name' => 'Updated Staff',
        ]);
    }

    public function test_can_delete_staff(): void
    {
        $response = $this->deleteJson("/api/v1/vendor/staff/{$this->vendorStaff->id}", [], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('vendorstaff', ['id' => $this->vendorStaff->id]);

        // Roles should be revoked for this vendor
        $this->assertCount(0, $this->staffUser->fresh()->roles);
    }
}
