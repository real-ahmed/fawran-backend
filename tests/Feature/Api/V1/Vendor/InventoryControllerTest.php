<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Catalog\Category;
use App\Models\Product\MasterProduct;
use App\Models\Product\VendorItem;
use App\Models\Product\VendorItemInventory;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private VendorItem $item;

    private VendorItemInventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        LocalAccount::create([
            'user_id' => $this->owner->id,
            'password' => Hash::make('password'),
        ]);

        // Must be a vendor type that supports inventory, e.g., Grocery
        $this->vendor = Vendor::factory()->grocery()->create([
            'owner_id' => $this->owner->id,
        ]);

        $this->setupVendorPermissions();

        $category = Category::create([
            'name' => ['en' => 'Test Category', 'ar' => 'تصنيف تجريبي'],
            'is_active' => true,
        ]);

        $masterProduct = MasterProduct::create([
            'name' => ['en' => 'Test Product', 'ar' => 'منتج تجريبي'],
            'category_id' => $category->id,
            'unit_type' => 'piece',
            'is_active' => true,
        ]);

        $this->item = VendorItem::create([
            'vendor_id' => $this->vendor->id,
            'master_product_id' => $masterProduct->id,
            'price' => 100.00,
            'is_available' => true,
        ]);

        $this->inventory = VendorItemInventory::create([
            'vendor_item_id' => $this->item->id,
            'current_stock' => 50,
            'low_stock_threshold' => 10,
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

    public function test_can_list_inventory(): void
    {
        $response = $this->getJson('/api/v1/vendor/inventory', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.vendor_item_id', $this->item->id)
            ->assertJsonPath('data.0.current_stock', 50);
    }

    public function test_can_update_inventory(): void
    {
        $response = $this->putJson("/api/v1/vendor/inventory/{$this->item->id}", [
            'current_stock' => 60,
            'low_stock_threshold' => 15,
        ], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.current_stock', 60)
            ->assertJsonPath('data.low_stock_threshold', 15);

        $this->assertDatabaseHas('vendor_item_inventory', [
            'vendor_item_id' => $this->item->id,
            'current_stock' => 60,
            'low_stock_threshold' => 15,
        ]);
    }

    public function test_can_adjust_inventory(): void
    {
        $response = $this->postJson('/api/v1/vendor/inventory/adjustments', [
            'vendor_item_id' => $this->item->id,
            'adjustment' => -5,
            'reason' => 'Sale',
        ], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.current_stock', 45); // 50 - 5

        $this->assertDatabaseHas('vendor_item_inventory', [
            'vendor_item_id' => $this->item->id,
            'current_stock' => 45,
        ]);
    }

    public function test_cannot_access_inventory_for_restaurant(): void
    {
        $restaurant = Vendor::factory()->restaurant()->create([
            'owner_id' => $this->owner->id,
        ]);

        $role = Role::create([
            'name' => 'owner_rest',
            'guard_name' => 'api',
            'vendor_id' => $restaurant->id,
        ]);
        $role->givePermissionTo(VendorPermission::values());
        $this->owner->assignRole($role);

        $response = $this->getJson('/api/v1/vendor/inventory', [
            'Authorization' => 'Bearer '.$this->token,
            'X-VENDOR-ID' => (string) $restaurant->id,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'This vendor type does not support inventory management.');
    }
}
