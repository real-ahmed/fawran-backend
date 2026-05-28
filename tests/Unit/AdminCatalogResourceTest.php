<?php

namespace Tests\Unit;

use App\Http\Resources\Admin\BrandResource;
use App\Http\Resources\Admin\CategoryResource;
use App\Http\Resources\Admin\MasterProductResource;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Product\MasterProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class AdminCatalogResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        Model::preventLazyLoading(false);

        parent::tearDown();
    }

    public function test_brand_resource_does_not_lazy_load_media(): void
    {
        Model::preventLazyLoading();

        $data = (new BrandResource(new Brand([
            'name' => ['en' => 'Test Brand'],
            'is_active' => true,
        ])))->toArray(Request::create('/'));

        $this->assertNull($data['image']);
    }

    public function test_category_resource_does_not_lazy_load_media(): void
    {
        Model::preventLazyLoading();

        $data = (new CategoryResource(new Category([
            'name' => ['en' => 'Test Category'],
            'is_active' => true,
        ])))->toArray(Request::create('/'));

        $this->assertNull($data['image']);
    }

    public function test_master_product_resource_does_not_lazy_load_media(): void
    {
        Model::preventLazyLoading();

        $data = (new MasterProductResource(new MasterProduct([
            'name' => ['en' => 'Test Product'],
            'unit_type' => 'piece',
            'is_active' => true,
        ])))->toArray(Request::create('/'));

        $this->assertNull($data['image']);
        $this->assertSame([], $data['images']);
        $this->assertArrayNotHasKey('created_at', $data);
    }
}
