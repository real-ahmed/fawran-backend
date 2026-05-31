<?php

namespace Tests\Feature\Finance;

use App\Models\Order\Order;
use App\Models\Order\SubOrder;
use App\Models\Platform\OrderCommission;
use App\Models\Platform\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorCustomCommission;
use App\Models\Vendor\VendorSubscription;
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

    public function test_calculates_with_fallback_commission_if_no_plan(): void
    {
        // No custom commission, no active plan
        [$order, $vendor] = $this->createOrderWithVendor(subTotal: 200.00);

        $this->calculator->calculateForOrder($order);

        $commission = OrderCommission::where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals('10.00', $commission->vendorcommission_percentage);
        $this->assertEquals('20.00', $commission->vendorcommission_amount); // 200 * 10%
    }

    public function test_calculates_with_active_subscription_plan(): void
    {
        [$order, $vendor] = $this->createOrderWithVendor(subTotal: 200.00);

        $plan = SubscriptionPlan::create([
            'name' => ['en' => 'Pro'],
            'monthly_price' => 500.00,
            'commission_percentage' => 5.00,
        ]);

        VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        // Refresh to pick up relation
        $order->loadMissing(['subOrders.vendor.activeSubscription.plan']);

        $this->calculator->calculateForOrder($order);

        $commission = OrderCommission::where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals('5.00', $commission->vendorcommission_percentage);
        $this->assertEquals('10.00', $commission->vendorcommission_amount); // 200 * 5%
    }

    public function test_calculates_with_custom_vendor_commission(): void
    {
        [$order, $vendor] = $this->createOrderWithVendor(subTotal: 300.00);

        // Active plan that shouldn't be used because custom commission takes precedence
        $plan = SubscriptionPlan::create([
            'name' => ['en' => 'Pro'],
            'monthly_price' => 500.00,
            'commission_percentage' => 5.00,
        ]);

        VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        // Set custom commission
        VendorCustomCommission::updateOrCreate(
            ['vendor_id' => $vendor->id],
            ['commission_percentage' => 15.00]
        );

        $order->load(['subOrders.vendor.customCommission', 'subOrders.vendor.activeSubscription.plan']);

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
        [$order, $vendor] = $this->createOrderWithVendor(subTotal: 100.00);

        $plan = SubscriptionPlan::create([
            'name' => ['en' => 'Pro'],
            'monthly_price' => 500.00,
            'commission_percentage' => 5.00,
        ]);

        $subscription = VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $order->load(['subOrders.vendor.activeSubscription.plan']);

        $this->calculator->calculateForOrder($order);

        // Change the plan setting
        $plan->update(['commission_percentage' => 25.00]);

        // Verify the frozen commission hasn't changed
        $commission = OrderCommission::where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        $this->assertEquals('5.00', $commission->vendorcommission_percentage);
        $this->assertEquals('5.00', $commission->vendorcommission_amount); // 100 * 5%
    }

    public function test_idempotent_calculation(): void
    {
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
