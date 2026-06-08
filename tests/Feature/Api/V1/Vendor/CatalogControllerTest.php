<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Enums\UnitType;
use App\Enums\VendorPermission;
use App\Models\Auth\LocalAccount;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Product\MasterProduct;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogControllerTest extends TestCase
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

    public function test_can_list_categories(): void
    {
        Category::create([
            'name' => ['en' => 'Drinks', 'ar' => 'مشروبات'],
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/vendor/catalog/categories', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_list_brands(): void
    {
        Brand::create([
            'name' => ['en' => 'Coca Cola', 'ar' => 'كوكا كولا'],
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/vendor/catalog/brands', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_list_master_products(): void
    {
        $category = Category::create([
            'name' => ['en' => 'Drinks', 'ar' => 'مشروبات'],
            'is_active' => true,
        ]);

        MasterProduct::create([
            'category_id' => $category->id,
            'name' => ['en' => 'Cola', 'ar' => 'كولا'],
            'unit_type' => UnitType::Piece,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/vendor/catalog/master-products', $this->vendorHeaders());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name.en', 'Cola');
    }

    public function test_can_store_master_product(): void
    {
        $category = Category::create([
            'name' => ['en' => 'Drinks', 'ar' => 'مشروبات'],
            'is_active' => true,
        ]);

        $brand = Brand::create([
            'name' => ['en' => 'BrandX', 'ar' => 'براند اكس'],
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/vendor/catalog/master-products', [
            'category_id' => $category->id,
            'name' => [
                'en' => 'New Burger',
                'ar' => 'برجر جديد',
            ],
            'unit_type' => UnitType::Piece->value,
            'brand_id' => $brand->id,
            'sku_barcode' => '123456789',
            'description' => [
                'en' => 'Delicious burger',
                'ar' => 'برجر لذيذ',
            ],
        ], $this->vendorHeaders());

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name.en', 'New Burger');

        $this->assertDatabaseHas('master_products', [
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('retail_product_details', [
            'brand_id' => $brand->id,
            'sku_barcode' => '123456789',
        ]);

        $this->assertDatabaseHas('master_product_descriptions', [
            'description->en' => 'Delicious burger',
        ]);

        $this->assertDatabaseHas('vendor_master_product_submissions', [
            'vendor_id' => $this->vendor->id,
            'status' => 'pending',
        ]);
    }
}
