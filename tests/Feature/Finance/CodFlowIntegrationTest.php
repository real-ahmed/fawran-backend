<?php

namespace Tests\Feature\Finance;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\CourierCashLimitExceeded;
use App\Events\OrderConfirmed;
use App\Events\OrderDelivered;
use App\Listeners\Finance\CalculateOrderCommissions;
use App\Listeners\Finance\RecordCourierCashCollection;
use App\Models\Address\UserAddress;
use App\Models\Courier\Courier;
use App\Models\Order\Delivery;
use App\Models\Order\Order;
use App\Models\Order\OrderDelivery;
use App\Models\Order\SubOrder;
use App\Models\Payment\CourierCashCollection;
use App\Models\Payment\Payment;
use App\Models\Platform\OrderCommission;
use App\Models\Platform\SystemSetting;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CodFlowIntegrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::updateOrCreate(['key' => 'default_vendorcommission'], ['value' => '10.00', 'group' => 'financial']);
        SystemSetting::updateOrCreate(['key' => 'courier_max_cash_hold_limit'], ['value' => '2000.00', 'group' => 'settlement']);
    }

    public function test_full_cod_flow(): void
    {
        [$order, $delivery, , $courier] = $this->createFullOrder();

        $commListener = app(CalculateOrderCommissions::class);
        $commListener->handle(new OrderConfirmed($order));
        $this->assertNotNull(OrderCommission::where('order_id', $order->id)->first());

        $codListener = app(RecordCourierCashCollection::class);
        $codListener->handle(new OrderDelivered($order, $delivery));

        $cash = CourierCashCollection::where('courier_id', $courier->id)->first();
        $this->assertNotNull($cash);
        $this->assertFalse($cash->is_settled);
        $this->assertEquals(PaymentStatus::Successful, $order->fresh()->payments->first()->status);
    }

    public function test_cash_limit_exceeded(): void
    {
        Event::fake([CourierCashLimitExceeded::class]);
        SystemSetting::where('key', 'courier_max_cash_hold_limit')->update(['value' => '100.00']);

        [$order, $delivery] = $this->createFullOrder(500.00);
        app(RecordCourierCashCollection::class)->handle(new OrderDelivered($order, $delivery));

        Event::assertDispatched(CourierCashLimitExceeded::class);
    }

    private function createFullOrder(float $total = 200.00): array
    {
        $owner = User::factory()->create();
        $customer = User::factory()->create();
        $courierUser = User::factory()->create();

        $vendor = Vendor::create(['owner_id' => $owner->id, 'name' => 'V', 'type' => 'restaurant', 'email' => fake()->unique()->safeEmail(), 'phone' => '010', 'latitude' => 30, 'longitude' => 31, 'formatted_address' => 'C', 'is_active' => true, 'status' => 'online']);
        $courier = Courier::create(['user_id' => $courierUser->id, 'national_id' => '29001010101010', 'vehicle_type' => 'motorcycle', 'plate_number' => 'X-1', 'is_online' => true]);

        $order = Order::create(['order_type' => 'delivery', 'total_products' => $total, 'status' => 'processing']);
        SubOrder::create(['order_id' => $order->id, 'vendor_id' => $vendor->id, 'sub_total' => $total, 'status' => 'preparing']);

        $address = UserAddress::create(['user_id' => $customer->id, 'formatted_address' => 'A', 'latitude' => 30, 'longitude' => 31, 'building_number' => '1', 'floor_number' => '1', 'apartment_number' => '1', 'phone' => '010', 'type' => 'home']);
        OrderDelivery::create(['order_id' => $order->id, 'address_id' => $address->id, 'total_delivery_fee' => 30]);

        $delivery = Delivery::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'fee_share' => 15, 'status' => 'completed']);
        Payment::create(['order_id' => $order->id, 'amount' => $total + 30, 'payment_method' => PaymentMethod::Cod, 'status' => PaymentStatus::Pending]);

        return [$order->fresh(), $delivery, $vendor, $courier];
    }
}
