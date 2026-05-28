<?php

namespace Tests\Unit;

use App\Builders\AdminBuilder;
use App\Builders\BrandBuilder;
use App\Builders\CategoryBuilder;
use App\Builders\CourierBuilder;
use App\Builders\DeliveryZoneBuilder;
use App\Builders\HotZoneBuilder;
use App\Builders\MasterProductBuilder;
use App\Builders\OrderBuilder;
use App\Builders\PayoutRequestBuilder;
use App\Builders\RefundRequestBuilder;
use App\Builders\RoleBuilder;
use App\Builders\SettlementBuilder;
use App\Builders\SystemSettingBuilder;
use App\Builders\UserBuilder;
use App\Builders\VendorBrandSubmissionBuilder;
use App\Builders\VendorBuilder;
use App\Builders\VendorCategorySubmissionBuilder;
use App\Builders\VendorMasterProductSubmissionBuilder;
use App\Models\Admin;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\VendorBrandSubmission;
use App\Models\Catalog\VendorCategorySubmission;
use App\Models\Courier\Courier;
use App\Models\Geo\DeliveryZone;
use App\Models\Geo\HotZone;
use App\Models\Order\Order;
use App\Models\Order\SubOrder;
use App\Models\Payment\PayoutRequest;
use App\Models\Payment\RefundRequest;
use App\Models\Payment\Settlement;
use App\Models\Platform\SystemSetting;
use App\Models\Product\MasterProduct;
use App\Models\Product\VendorMasterProductSubmission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomBuilderTest extends TestCase
{
    /**
     * @param  class-string  $model
     * @param  class-string  $builder
     */
    #[DataProvider('modelBuilderProvider')]
    public function test_models_use_their_custom_builders(string $model, string $builder): void
    {
        $this->assertInstanceOf($builder, $model::query());
    }

    /**
     * @return array<string, array{model: class-string, builder: class-string}>
     */
    public static function modelBuilderProvider(): array
    {
        return [
            'admin' => ['model' => Admin::class, 'builder' => AdminBuilder::class],
            'brand' => ['model' => Brand::class, 'builder' => BrandBuilder::class],
            'category' => ['model' => Category::class, 'builder' => CategoryBuilder::class],
            'courier' => ['model' => Courier::class, 'builder' => CourierBuilder::class],
            'delivery zone' => ['model' => DeliveryZone::class, 'builder' => DeliveryZoneBuilder::class],
            'hot zone' => ['model' => HotZone::class, 'builder' => HotZoneBuilder::class],
            'master product' => ['model' => MasterProduct::class, 'builder' => MasterProductBuilder::class],
            'order' => ['model' => Order::class, 'builder' => OrderBuilder::class],
            'payout request' => ['model' => PayoutRequest::class, 'builder' => PayoutRequestBuilder::class],
            'refund request' => ['model' => RefundRequest::class, 'builder' => RefundRequestBuilder::class],
            'role' => ['model' => Role::class, 'builder' => RoleBuilder::class],
            'settlement' => ['model' => Settlement::class, 'builder' => SettlementBuilder::class],
            'system setting' => ['model' => SystemSetting::class, 'builder' => SystemSettingBuilder::class],
            'user' => ['model' => User::class, 'builder' => UserBuilder::class],
            'vendor' => ['model' => Vendor::class, 'builder' => VendorBuilder::class],
            'vendor brand submission' => ['model' => VendorBrandSubmission::class, 'builder' => VendorBrandSubmissionBuilder::class],
            'vendor category submission' => ['model' => VendorCategorySubmission::class, 'builder' => VendorCategorySubmissionBuilder::class],
            'vendor master product submission' => ['model' => VendorMasterProductSubmission::class, 'builder' => VendorMasterProductSubmissionBuilder::class],
        ];
    }

    /**
     * @param  class-string  $model
     */
    #[DataProvider('newestModelProvider')]
    public function test_list_builders_order_models_newest_first(string $model): void
    {
        $query = $model::query()->newest();

        $this->assertStringContainsString('order by', $query->toSql());
        $this->assertStringContainsString(' desc', $query->toSql());
    }

    /**
     * @return array<string, array{model: class-string}>
     */
    public static function newestModelProvider(): array
    {
        return [
            'admin' => ['model' => Admin::class],
            'brand' => ['model' => Brand::class],
            'category' => ['model' => Category::class],
            'courier' => ['model' => Courier::class],
            'delivery zone' => ['model' => DeliveryZone::class],
            'hot zone' => ['model' => HotZone::class],
            'master product' => ['model' => MasterProduct::class],
            'order' => ['model' => Order::class],
            'payout request' => ['model' => PayoutRequest::class],
            'refund request' => ['model' => RefundRequest::class],
            'role' => ['model' => Role::class],
            'settlement' => ['model' => Settlement::class],
            'user' => ['model' => User::class],
            'vendor' => ['model' => Vendor::class],
            'vendor brand submission' => ['model' => VendorBrandSubmission::class],
            'vendor category submission' => ['model' => VendorCategorySubmission::class],
            'vendor master product submission' => ['model' => VendorMasterProductSubmission::class],
        ];
    }

    public function test_courier_builder_applies_search_filters_and_list_relations(): void
    {
        $query = Courier::query()
            ->withListRelations()
            ->search('ahmed')
            ->online(true)
            ->vehicleType('motorcycle')
            ->inDeliveryZone(5)
            ->approvalStatus('pending');

        $this->assertSame(['user', 'document', 'approval'], array_keys($query->getEagerLoads()));
        $this->assertStringContainsString('plate_number', $query->toSql());
        $this->assertStringContainsString('not exists', $query->toSql());
        $this->assertStringContainsString('`rejected_at` is null', $query->toSql());
        $this->assertContains('%ahmed%', $query->getBindings());
        $this->assertContains(true, $query->getBindings());
        $this->assertContains('motorcycle', $query->getBindings());
        $this->assertContains(5, $query->getBindings());
    }

    public function test_courier_builder_applies_rejected_status_filter(): void
    {
        $query = Courier::query()->approvalStatus('rejected');

        $this->assertStringContainsString('not exists', $query->toSql());
        $this->assertStringContainsString('`rejected_at` is not null', $query->toSql());
    }

    public function test_brand_builder_approved_status_means_active_without_vendor_submission(): void
    {
        $query = Brand::query()
            ->withListRelations()
            ->approvalStatus('approved')
            ->active(true);

        $this->assertContains('media', array_keys($query->getEagerLoads()));
        $this->assertContains('vendorSubmission.vendor', array_keys($query->getEagerLoads()));
        $this->assertStringContainsString('`is_active` = ?', $query->toSql());
        $this->assertStringContainsString('not exists', $query->toSql());
        $this->assertSame([true, true], $query->getBindings());
    }

    public function test_category_and_master_product_builders_eager_load_media_for_image_resources(): void
    {
        $categoryQuery = Category::query()->withListRelations();
        $productQuery = MasterProduct::query()
            ->withListRelations()
            ->newest();

        $this->assertContains('media', array_keys($categoryQuery->getEagerLoads()));
        $this->assertContains('media', array_keys($productQuery->getEagerLoads()));
        $this->assertStringContainsString('order by `id` desc', $productQuery->toSql());
        $this->assertStringNotContainsString('created_at', $productQuery->toSql());
    }

    public function test_user_builder_applies_identity_search_and_active_filter(): void
    {
        $query = User::query()
            ->searchIdentity('mona')
            ->active(false);

        $this->assertStringContainsString('name', $query->toSql());
        $this->assertStringContainsString('is_active', $query->toSql());
        $this->assertContains('%mona%', $query->getBindings());
        $this->assertContains(false, $query->getBindings());
    }

    public function test_system_setting_builder_applies_group_and_ordering(): void
    {
        $query = SystemSetting::query()
            ->key('app_name')
            ->keys(['app_name', 'currency'])
            ->group('general')
            ->ordered();

        $this->assertStringContainsString('where `key` = ?', $query->toSql());
        $this->assertStringContainsString('`key` in (?, ?)', $query->toSql());
        $this->assertStringContainsString('`group` = ?', $query->toSql());
        $this->assertStringContainsString('order by `group` asc, `key` asc', $query->toSql());
        $this->assertSame(['app_name', 'app_name', 'currency', 'general'], $query->getBindings());
    }

    public function test_delivery_zone_builder_applies_filters_and_spatial_point_lookup(): void
    {
        $query = DeliveryZone::query()
            ->withPolygonGeoJson()
            ->filter([
                'is_active' => 'true',
                'search' => 'riyadh',
            ])
            ->containsPoint(24.7136, 46.6753)
            ->newest();

        $this->assertStringContainsString('ST_AsGeoJSON', $query->toSql());
        $this->assertStringContainsString('`is_active` = ?', $query->toSql());
        $this->assertStringContainsString('json_unquote', $query->toSql());
        $this->assertStringContainsString('ST_Contains', $query->toSql());
        $this->assertStringContainsString('order by `id` desc', $query->toSql());
        $this->assertContains(true, $query->getBindings());
        $this->assertContains('%riyadh%', $query->getBindings());
        $this->assertContains('POINT(46.6753 24.7136)', $query->getBindings());
    }

    public function test_vendor_submission_builders_apply_pending_approval_list_shape(): void
    {
        $brandQuery = VendorBrandSubmission::query()
            ->withApprovalRelations()
            ->pending()
            ->newest();
        $categoryQuery = VendorCategorySubmission::query()
            ->withApprovalRelations()
            ->pending()
            ->newest();
        $productQuery = VendorMasterProductSubmission::query()
            ->withApprovalRelations()
            ->pending()
            ->newest();

        $this->assertSame(['brand', 'vendor'], array_keys($brandQuery->getEagerLoads()));
        $this->assertSame(['category', 'vendor'], array_keys($categoryQuery->getEagerLoads()));
        $this->assertSame(['masterProduct', 'vendor'], array_keys($productQuery->getEagerLoads()));
        $this->assertStringContainsString('`status` = ?', $brandQuery->toSql());
        $this->assertStringContainsString('order by', $categoryQuery->toSql());
        $this->assertSame(['pending'], $productQuery->getBindings());
    }

    public function test_sub_order_exposes_vendor_relationship_for_order_eager_loading(): void
    {
        $relation = (new SubOrder)->vendor();
        $eagerLoads = array_keys(Order::query()->withListRelations()->getEagerLoads());

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Vendor::class, $relation->getRelated());
        $this->assertSame('vendor_id', $relation->getForeignKeyName());
        $this->assertContains('subOrders.vendor', $eagerLoads);
        $this->assertContains('subOrders.items.storeItem.masterProduct', $eagerLoads);
        $this->assertContains('subOrders.items.options.productOption', $eagerLoads);
        $this->assertContains('subOrders.items.options.productOptionValue', $eagerLoads);
        $this->assertContains('subOrders.items.note', $eagerLoads);
    }
}
