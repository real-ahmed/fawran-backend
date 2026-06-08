<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\UnitType;
use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Catalog\Category;
use App\Models\Product\MasterProduct;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorItemControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private MasterProduct $masterProduct;

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

        $category = Category::create([
            'name' => ['en' => 'Food', 'ar' => 'طعام'],
            'is_active' => true,
        ]);

        $this->masterProduct = MasterProduct::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Burger', 'ar' => 'برجر'],
            'unit_type' => UnitType::Piece,
            'is_active' => true,
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

    public function test_can_list_vendor_items(): void
    {
        $this->vendor->storeItems()->create([
            'master_product_id' => $this->masterProduct->id,
            'price' => 10.50,
        ]);

        $response = $this->getJson('/api/v1/vendor/items', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.price', 10.50);
    }

    public function test_can_create_vendor_item(): void
    {
        $response = $this->postJson('/api/v1/vendor/items', [
            'master_product_id' => $this->masterProduct->id,
            'price' => 15.50,
            'is_available' => true,
            'preparation_time' => 30, // restaurant specific
        ], $this->vendorHeaders());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.price', 15.50)
            ->assertJsonPath('data.preparation_time', 30);

        $this->assertDatabaseHas('vendor_items', [
            'vendor_id' => $this->vendor->id,
            'master_product_id' => $this->masterProduct->id,
            'price' => 15.50,
        ]);

        $this->assertDatabaseHas('restaurant_dish_details', [
            'vendor_item_id' => $response->json('data.id'),
            'preparation_time' => 30,
        ]);
    }

    public function test_can_update_vendor_item(): void
    {
        $item = $this->vendor->storeItems()->create([
            'master_product_id' => $this->masterProduct->id,
            'price' => 10.50,
        ]);

        $response = $this->putJson("/api/v1/vendor/items/{$item->id}", [
            'price' => 20.50,
            'preparation_time' => 45,
        ], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('data.price', 20.50)
            ->assertJsonPath('data.preparation_time', 45);

        $this->assertDatabaseHas('vendor_items', [
            'id' => $item->id,
            'price' => 20.50,
        ]);
    }

    public function test_can_delete_vendor_item(): void
    {
        $item = $this->vendor->storeItems()->create([
            'master_product_id' => $this->masterProduct->id,
            'price' => 10.50,
        ]);

        $response = $this->deleteJson("/api/v1/vendor/items/{$item->id}", [], $this->vendorHeaders());

        $response->assertOk();
        $this->assertDatabaseMissing('vendor_items', ['id' => $item->id]);
    }

    public function test_can_update_item_status(): void
    {
        $item = $this->vendor->storeItems()->create([
            'master_product_id' => $this->masterProduct->id,
            'price' => 10.50,
            'is_available' => true,
        ]);

        $response = $this->putJson("/api/v1/vendor/items/{$item->id}/status", [
            'is_available' => false,
        ], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('data.is_available', false);

        $this->assertDatabaseHas('vendor_items', [
            'id' => $item->id,
            'is_available' => false,
        ]);
    }

    public function test_cannot_access_other_vendor_items(): void
    {
        $otherVendor = Vendor::factory()->create();
        $otherItem = $otherVendor->storeItems()->create([
            'master_product_id' => $this->masterProduct->id,
            'price' => 10.50,
        ]);

        $response = $this->getJson("/api/v1/vendor/items/{$otherItem->id}", $this->vendorHeaders());

        $response->assertForbidden();
    }
}
