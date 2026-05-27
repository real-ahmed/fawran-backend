<?php

namespace Tests\Unit;

use App\Builders\AdminBuilder;
use App\Builders\BrandBuilder;
use App\Builders\CategoryBuilder;
use App\Builders\CourierBuilder;
use App\Builders\HotZoneBuilder;
use App\Builders\MasterProductBuilder;
use App\Builders\OrderBuilder;
use App\Builders\PayoutRequestBuilder;
use App\Builders\RefundRequestBuilder;
use App\Builders\RoleBuilder;
use App\Builders\SettlementBuilder;
use App\Builders\SystemSettingBuilder;
use App\Builders\UserBuilder;
use App\Builders\VendorBuilder;
use App\Models\Admin;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Courier\Courier;
use App\Models\Geo\HotZone;
use App\Models\Order\Order;
use App\Models\Payment\PayoutRequest;
use App\Models\Payment\RefundRequest;
use App\Models\Payment\Settlement;
use App\Models\Platform\SystemSetting;
use App\Models\Product\MasterProduct;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor\Vendor;
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
        $this->assertContains('%ahmed%', $query->getBindings());
        $this->assertContains(true, $query->getBindings());
        $this->assertContains('motorcycle', $query->getBindings());
        $this->assertContains(5, $query->getBindings());
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
            ->group('general')
            ->ordered();

        $this->assertStringContainsString('where `group` = ?', $query->toSql());
        $this->assertStringContainsString('order by `group` asc, `key` asc', $query->toSql());
        $this->assertSame(['general'], $query->getBindings());
    }
}
