<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\VendorPermission;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Models\Auth\LocalAccount;
use App\Models\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorStaff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorProfileControllerTest extends TestCase
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

        // Create and assign permissions
        $this->setupVendorPermissions();

        $this->token = auth('api')->login($this->owner);
    }

    private function setupVendorPermissions(): void
    {
        // Create all vendor permissions
        foreach (VendorPermission::values() as $permissionName) {
            Permission::findOrCreate($permissionName, 'api');
        }

        // Create an owner role with all permissions
        $role = Role::create([
            'name' => 'owner',
            'guard_name' => 'api',
            'vendor_id' => $this->vendor->id,
        ]);
        $role->givePermissionTo(VendorPermission::values());

        // Assign role to user
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

    // ─── Show Profile ────────────────────────────────────────────

    public function test_vendor_can_view_own_profile(): void
    {
        $response = $this->getJson('/api/v1/vendor/profile', $this->vendorHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->vendor->id)
            ->assertJsonPath('data.type', VendorType::RESTAURANT->value);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        auth('api')->logout();

        $response = $this->getJson('/api/v1/vendor/profile');

        $response->assertUnauthorized();
    }

    // ─── Update Profile ──────────────────────────────────────────

    public function test_vendor_can_update_profile_name(): void
    {
        $response = $this->putJson('/api/v1/vendor/profile', [
            'name' => ['en' => 'New Name', 'ar' => 'اسم جديد'],
        ], $this->vendorHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name.en', 'New Name')
            ->assertJsonPath('data.name.ar', 'اسم جديد');
    }

    public function test_vendor_can_update_description(): void
    {
        $response = $this->putJson('/api/v1/vendor/profile', [
            'description' => ['en' => 'Best restaurant', 'ar' => 'افضل مطعم'],
        ], $this->vendorHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.description.en', 'Best restaurant')
            ->assertJsonPath('data.description.ar', 'افضل مطعم');
    }

    public function test_vendor_can_update_profile_image(): void
    {
        Storage::fake('public');

        $response = $this->putJson('/api/v1/vendor/profile', [
            'image' => UploadedFile::fake()->image('vendor.jpg'),
        ], $this->vendorHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('media', 1);
    }

    public function test_update_profile_validates_unique_email(): void
    {
        Vendor::factory()->create(['email' => 'taken@example.com']);

        $response = $this->putJson('/api/v1/vendor/profile', [
            'email' => 'taken@example.com',
        ], $this->vendorHeaders());

        $response->assertUnprocessable();
    }

    // ─── Update Status ───────────────────────────────────────────

    public function test_vendor_can_toggle_status_to_online(): void
    {
        $response = $this->putJson('/api/v1/vendor/profile/status', [
            'status' => 'online',
        ], $this->vendorHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('data.status', VendorStatus::ONLINE->value);
    }

    public function test_vendor_can_toggle_status_to_busy(): void
    {
        $response = $this->putJson('/api/v1/vendor/profile/status', [
            'status' => 'busy',
        ], $this->vendorHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('data.status', VendorStatus::BUSY->value);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $response = $this->putJson('/api/v1/vendor/profile/status', [
            'status' => 'invalid',
        ], $this->vendorHeaders());

        $response->assertUnprocessable();
    }

    // ─── Working Hours ───────────────────────────────────────────

    public function test_vendor_can_set_working_hours(): void
    {
        $response = $this->putJson('/api/v1/vendor/profile/working-hours', [
            'working_hours' => [
                ['day_of_week' => 0, 'open_time' => '08:00', 'close_time' => '22:00'],
                ['day_of_week' => 1, 'open_time' => '09:00', 'close_time' => '21:00'],
            ],
        ], $this->vendorHeaders());

        $response->assertOk();
        $this->assertDatabaseCount('vendor_working_hours', 2);
    }

    public function test_working_hours_replaces_existing(): void
    {
        // Create initial hours
        $this->vendor->workingHours()->createMany([
            ['day_of_week' => 0, 'open_time' => '08:00', 'close_time' => '22:00'],
            ['day_of_week' => 1, 'open_time' => '08:00', 'close_time' => '22:00'],
            ['day_of_week' => 2, 'open_time' => '08:00', 'close_time' => '22:00'],
        ]);

        $response = $this->putJson('/api/v1/vendor/profile/working-hours', [
            'working_hours' => [
                ['day_of_week' => 5, 'open_time' => '10:00', 'close_time' => '23:00'],
            ],
        ], $this->vendorHeaders());

        $response->assertOk();
        $this->assertDatabaseCount('vendor_working_hours', 1);
    }

    public function test_working_hours_validates_time_format(): void
    {
        $response = $this->putJson('/api/v1/vendor/profile/working-hours', [
            'working_hours' => [
                ['day_of_week' => 0, 'open_time' => 'invalid', 'close_time' => '22:00'],
            ],
        ], $this->vendorHeaders());

        $response->assertUnprocessable();
    }

    // ─── Authorization ───────────────────────────────────────────

    public function test_staff_without_permission_cannot_update_profile(): void
    {
        $staffUser = User::factory()->create();
        LocalAccount::create([
            'user_id' => $staffUser->id,
            'password' => Hash::make('password'),
        ]);

        VendorStaff::factory()->create([
            'user_id' => $staffUser->id,
            'vendor_id' => $this->vendor->id,
        ]);

        // Create a role without MANAGE_PROFILE permission
        $viewerRole = Role::create([
            'name' => 'viewer',
            'guard_name' => 'api',
            'vendor_id' => $this->vendor->id,
        ]);
        $viewerRole->givePermissionTo(VendorPermission::VIEW_DASHBOARD->value);

        setPermissionsTeamId($this->vendor->id);
        $staffUser->assignRole($viewerRole);

        $staffToken = auth('api')->login($staffUser);

        $response = $this->putJson('/api/v1/vendor/profile', [
            'name' => ['en' => 'Hacked', 'ar' => 'مخترق'],
        ], [
            'Authorization' => 'Bearer '.$staffToken,
            'X-VENDOR-ID' => (string) $this->vendor->id,
        ]);

        $response->assertForbidden();
    }

    public function test_vendor_from_another_store_cannot_access(): void
    {
        $otherOwner = User::factory()->create();
        LocalAccount::create([
            'user_id' => $otherOwner->id,
            'password' => Hash::make('password'),
        ]);

        $otherVendor = Vendor::factory()->create(['owner_id' => $otherOwner->id]);
        $otherToken = auth('api')->login($otherOwner);

        // Try to access original vendor's profile with other vendor's X-VENDOR-ID
        $response = $this->getJson('/api/v1/vendor/profile', [
            'Authorization' => 'Bearer '.$otherToken,
            'X-VENDOR-ID' => (string) $this->vendor->id,
        ]);

        $response->assertForbidden();
    }
}
