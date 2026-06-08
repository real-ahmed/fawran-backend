<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\PurchaseOrderStatus;
use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Catalog\Category;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
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

class PurchaseOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private Supplier $supplier;

    private VendorItem $vendorItem;

    private PurchaseOrder $purchaseOrder;

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

        $this->vendorItem = VendorItem::create([
            'vendor_id' => $this->vendor->id,
            'master_product_id' => $masterProduct->id,
            'price' => 100.00,
            'is_available' => true,
        ]);

        VendorItemInventory::create([
            'vendor_item_id' => $this->vendorItem->id,
            'current_stock' => 10,
            'low_stock_threshold' => 5,
        ]);

        $this->purchaseOrder = PurchaseOrder::create([
            'vendor_id' => $this->vendor->id,
            'supplier_id' => $this->supplier->id,
            'total_cost' => 150.50,
            'status' => PurchaseOrderStatus::Pending,
        ]);

        $this->purchaseOrder->items()->create([
            'vendor_item_id' => $this->vendorItem->id,
            'quantity' => 10,
            'cost_price' => 15.05,
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

    public function test_can_list_purchase_orders(): void
    {
        $response = $this->getJson('/api/v1/vendor/purchase-orders', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->purchaseOrder->id);
    }

    public function test_can_show_purchase_order(): void
    {
        $response = $this->getJson("/api/v1/vendor/purchase-orders/{$this->purchaseOrder->id}", $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->purchaseOrder->id)
            ->assertJsonPath('data.items.0.quantity', 10);
    }

    public function test_can_create_purchase_order(): void
    {
        $response = $this->postJson('/api/v1/vendor/purchase-orders', [
            'supplier_id' => $this->supplier->id,
            'items' => [
                [
                    'vendor_item_id' => $this->vendorItem->id,
                    'quantity' => 50,
                    'cost_price' => 10,
                ],
            ],
        ], $this->vendorHeaders());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_cost', 500)
            ->assertJsonPath('data.status', PurchaseOrderStatus::Pending->value);

        $this->assertDatabaseHas('purchase_orders', [
            'vendor_id' => $this->vendor->id,
            'total_cost' => 500,
        ]);
    }

    public function test_can_update_purchase_order_status_to_received(): void
    {
        // Initial stock is 10
        $this->assertEquals(10, $this->vendorItem->inventory->current_stock);

        $response = $this->putJson("/api/v1/vendor/purchase-orders/{$this->purchaseOrder->id}/status", [
            'status' => PurchaseOrderStatus::Received->value,
        ], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', PurchaseOrderStatus::Received->value);

        // Stock should be increased by 10 (the quantity in the purchase order)
        $this->assertEquals(20, $this->vendorItem->inventory->fresh()->current_stock);
    }

    public function test_cannot_update_received_purchase_order(): void
    {
        $this->purchaseOrder->update(['status' => PurchaseOrderStatus::Received]);

        $response = $this->putJson("/api/v1/vendor/purchase-orders/{$this->purchaseOrder->id}/status", [
            'status' => PurchaseOrderStatus::Pending->value,
        ], $this->vendorHeaders());

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
