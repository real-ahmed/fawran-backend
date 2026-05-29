<?php

namespace Tests\Feature\Finance;

use App\Models\Order\Order;
use App\Models\Order\SubOrder;
use App\Models\Platform\OrderCommission;
use App\Models\Platform\SystemSetting;
use App\Models\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorCustomCommission;
use App\Services\Finance\CommissionCalculator;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CommissionCalculatorTest extends TestCase
{
    use LazilyRefreshDatabase;

    private CommissionCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = app(CommissionCalculator::class);
    }

    public function test_calculates_with_default_commission(): void
    {
        // Ensure the setting exists
        SystemSetting::updateOrCreate(
            ['key' => 'default_vendorcommission'],
            ['value' => '10.00', 'group' => 'financial']
        );

        [$order, $vendor] = $this->createOrderWithVendor(subTotal: 200.00);

        $this->calculator->calculateForOrder($order);

        $commission = OrderCommission::where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals('10.00', $commission->vendorcommission_percentage);
        $this->assertEquals('20.00', $commission->vendorcommission_amount); // 200 * 10%
    }

    public function test_calculates_with_custom_vendor_commission(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'default_vendorcommission'],
            ['value' => '10.00', 'group' => 'financial']
        );

        [$order, $vendor] = $this->createOrderWithVendor(subTotal: 300.00);

        // Set custom commission
        VendorCustomCommission::updateOrCreate(
            ['vendor_id' => $vendor->id],
            ['commission_percentage' => 15.00]
        );

        // Refresh to pick up commission
        $order->load('subOrders.vendor.customCommission');

        $this->calculator->calculateForOrder($order);

        $commission = OrderCommission::where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals('15.00', $commission->vendorcommission_percentage);
        $this->assertEquals('45.00', $commission->vendorcommission_amount); // 300 * 15%
    }

    public function test_freeze_commission_values_on_settings_change(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'default_vendorcommission'],
            ['value' => '10.00', 'group' => 'financial']
        );

        [$order, $vendor] = $this->createOrderWithVendor(subTotal: 100.00);

        $this->calculator->calculateForOrder($order);

        // Change the system setting
        SystemSetting::where('key', 'default_vendorcommission')->update(['value' => '25.00']);

        // Verify the frozen commission hasn't changed
        $commission = OrderCommission::where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        $this->assertEquals('10.00', $commission->vendorcommission_percentage);
        $this->assertEquals('10.00', $commission->vendorcommission_amount); // 100 * 10%
    }

    public function test_idempotent_calculation(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'default_vendorcommission'],
            ['value' => '10.00', 'group' => 'financial']
        );

        [$order, $vendor] = $this->createOrderWithVendor(subTotal: 100.00);

        // Calculate twice
        $this->calculator->calculateForOrder($order);
        $this->calculator->calculateForOrder($order);

        // Should only have one record
        $count = OrderCommission::where('order_id', $order->id)->count();
        $this->assertEquals(1, $count);
    }

    /**
     * @return array{0: Order, 1: Vendor}
     */
    private function createOrderWithVendor(float $subTotal): array
    {
        $user = User::factory()->create();

        $vendor = Vendor::create([
            'owner_id' => $user->id,
            'name' => fake()->company(),
            'type' => 'restaurant',
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'formatted_address' => 'Cairo',
            'is_active' => true,
            'status' => 'online',
        ]);

        $order = Order::create([
            'order_type' => 'delivery',
            'total_products' => $subTotal,
            'status' => 'processing',
        ]);

        SubOrder::create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'sub_total' => $subTotal,
            'status' => 'preparing',
        ]);

        return [$order->fresh(), $vendor];
    }
}
