<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\UnitType;
use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Catalog\Category;
use App\Models\Product\MasterProduct;
use App\Models\Product\VendorItem;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductOptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private string $token;

    private VendorItem $vendorItem;

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

        $masterProduct = MasterProduct::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Burger', 'ar' => 'برجر'],
            'unit_type' => UnitType::Piece,
            'is_active' => true,
        ]);

        $this->vendorItem = $this->vendor->storeItems()->create([
            'master_product_id' => $masterProduct->id,
            'price' => 15.50,
            'is_available' => true,
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
            'Authorization' => "Bearer {$this->token}",
            'X-VENDOR-ID' => $this->vendor->id,
        ];
    }

    public function test_can_list_product_options(): void
    {
        $option = $this->vendorItem->productOptions()->create([
            'name' => ['en' => 'Size', 'ar' => 'الحجم'],
            'is_required' => true,
            'max_selections' => 1,
        ]);

        $option->values()->create([
            'name' => ['en' => 'Large', 'ar' => 'كبير'],
            'additional_price' => 5.00,
        ]);

        $response = $this->getJson("/api/v1/vendor/items/{$this->vendorItem->id}/options", $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name.en', 'Size')
            ->assertJsonCount(1, 'data.0.values');
    }

    public function test_can_create_product_option(): void
    {
        $response = $this->postJson("/api/v1/vendor/items/{$this->vendorItem->id}/options", [
            'name' => ['en' => 'Toppings', 'ar' => 'إضافات'],
            'is_required' => false,
            'max_selections' => 3,
            'values' => [
                [
                    'name' => ['en' => 'Cheese', 'ar' => 'جبن'],
                    'additional_price' => 2.50,
                    'is_available' => true,
                ],
                [
                    'name' => ['en' => 'Bacon', 'ar' => 'بيكون'],
                    'additional_price' => 4.00,
                    'is_available' => true,
                ],
            ],
        ], $this->vendorHeaders());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name.en', 'Toppings')
            ->assertJsonCount(2, 'data.values');

        $this->assertDatabaseHas('product_options', [
            'vendor_item_id' => $this->vendorItem->id,
            'is_required' => false,
            'max_selections' => 3,
        ]);

        $this->assertDatabaseHas('product_option_values', [
            'additional_price' => 2.50,
        ]);
    }

    public function test_can_update_product_option(): void
    {
        $option = $this->vendorItem->productOptions()->create([
            'name' => ['en' => 'Size', 'ar' => 'الحجم'],
            'is_required' => true,
            'max_selections' => 1,
        ]);

        $value = $option->values()->create([
            'name' => ['en' => 'Small', 'ar' => 'صغير'],
            'additional_price' => 0.00,
        ]);

        $response = $this->putJson("/api/v1/vendor/items/{$this->vendorItem->id}/options/{$option->id}", [
            'name' => ['en' => 'Drink Size', 'ar' => 'حجم المشروب'],
            'values' => [
                [
                    'id' => $value->id,
                    'name' => ['en' => 'Small', 'ar' => 'صغير'],
                    'additional_price' => 1.00,
                ],
                [
                    'name' => ['en' => 'Medium', 'ar' => 'وسط'],
                    'additional_price' => 3.00,
                ],
            ],
        ], $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('data.name.en', 'Drink Size')
            ->assertJsonCount(2, 'data.values');

        $this->assertDatabaseHas('product_option_values', [
            'id' => $value->id,
            'additional_price' => 1.00,
        ]);
    }

    public function test_can_delete_product_option(): void
    {
        $option = $this->vendorItem->productOptions()->create([
            'name' => ['en' => 'Size', 'ar' => 'الحجم'],
        ]);

        $response = $this->deleteJson("/api/v1/vendor/items/{$this->vendorItem->id}/options/{$option->id}", [], $this->vendorHeaders());

        $response->assertOk();

        $this->assertDatabaseMissing('product_options', [
            'id' => $option->id,
        ]);
    }
}
