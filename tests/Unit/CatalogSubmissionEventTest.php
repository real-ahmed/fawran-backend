<?php

namespace Tests\Unit;

use App\Events\Catalog\BrandSubmitted;
use App\Events\Catalog\CategorySubmitted;
use App\Events\Catalog\MasterProductSubmitted;
use App\Models\Admin;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Product\MasterProduct;
use Tests\TestCase;

class CatalogSubmissionEventTest extends TestCase
{
    public function test_brand_submission_event_uses_brand_payload(): void
    {
        $admin = $this->admin();
        $brand = new Brand(['name' => ['en' => 'Acme']]);
        $brand->id = 12;

        $payload = (new BrandSubmitted($brand, $admin))->broadcastWith();

        $this->assertSame('Brand', $payload['item_type']);
        $this->assertSame(12, $payload['item_id']);
        $this->assertSame('brands', $payload['resource']);
        $this->assertStringContainsString('Acme', $payload['message']);
    }

    public function test_category_submission_event_uses_category_payload(): void
    {
        $admin = $this->admin();
        $category = new Category(['name' => ['en' => 'Groceries']]);
        $category->id = 22;

        $payload = (new CategorySubmitted($category, $admin))->broadcastWith();

        $this->assertSame('Category', $payload['item_type']);
        $this->assertSame(22, $payload['item_id']);
        $this->assertSame('categories', $payload['resource']);
        $this->assertStringContainsString('Groceries', $payload['message']);
    }

    public function test_master_product_submission_event_uses_master_product_payload(): void
    {
        $admin = $this->admin();
        $product = new MasterProduct(['name' => ['en' => 'Apples']]);
        $product->id = 32;

        $payload = (new MasterProductSubmitted($product, $admin))->broadcastWith();

        $this->assertSame('Master Product', $payload['item_type']);
        $this->assertSame(32, $payload['item_id']);
        $this->assertSame('master-products', $payload['resource']);
        $this->assertStringContainsString('Apples', $payload['message']);
    }

    private function admin(): Admin
    {
        $admin = new class extends Admin
        {
            public function preferredLocale(): string
            {
                return 'en';
            }
        };

        $admin->id = 7;

        return $admin;
    }
}
