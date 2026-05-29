<?php

namespace Tests\Feature\Finance;

use App\Models\Address\UserAddress;
use App\Models\Courier\Courier;
use App\Models\Order\Delivery;
use App\Models\Order\Order;
use App\Models\Order\OrderDelivery;
use App\Models\Payment\CourierCashCollection;
use App\Models\Platform\SystemSetting;
use App\Models\User;
use App\Services\Finance\CashCollectionService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CashCollectionServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private CashCollectionService $cashService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cashService = app(CashCollectionService::class);
    }

    public function test_record_collection_creates_correct_record(): void
    {
        [$order, $delivery] = $this->createDeliveredOrder(
            totalProducts: 150.00,
            deliveryFee: 35.00,
            courierFee: 15.00,
        );

        $collection = $this->cashService->recordCollection($order, $delivery);

        $this->assertNotNull($collection);
        $this->assertEquals($delivery->courier_id, $collection->courier_id);
        $this->assertEquals('185.00', $collection->amount_collected); // 150 + 35
        $this->assertEquals('15.00', $collection->courier_fee_share);
        $this->assertEquals('170.00', $collection->amount_owed_to_platform); // 185 - 15
        $this->assertFalse($collection->is_settled);
    }

    public function test_get_unsettled_total_calculates_correctly(): void
    {
        $courier = $this->createCourier();

        CourierCashCollection::create([
            'courier_id' => $courier->id,
            'source_type' => Order::class,
            'source_id' => 1,
            'amount_collected' => 100,
            'courier_fee_share' => 10,
            'amount_owed_to_platform' => 90,
            'is_settled' => false,
            'collected_at' => now(),
        ]);

        CourierCashCollection::create([
            'courier_id' => $courier->id,
            'source_type' => Order::class,
            'source_id' => 2,
            'amount_collected' => 200,
            'courier_fee_share' => 20,
            'amount_owed_to_platform' => 180,
            'is_settled' => false,
            'collected_at' => now(),
        ]);

        // One settled collection (should be excluded)
        CourierCashCollection::create([
            'courier_id' => $courier->id,
            'source_type' => Order::class,
            'source_id' => 3,
            'amount_collected' => 500,
            'courier_fee_share' => 50,
            'amount_owed_to_platform' => 450,
            'is_settled' => true,
            'collected_at' => now(),
        ]);

        $total = $this->cashService->getUnsettledTotal($courier);

        $this->assertEquals(270.00, $total); // 90 + 180
    }

    public function test_cash_limit_detection(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'courier_max_cash_hold_limit'],
            ['value' => '500.00', 'group' => 'settlement']
        );

        $courier = $this->createCourier();

        // Add collections totaling 600 (over 500 limit)
        CourierCashCollection::create([
            'courier_id' => $courier->id,
            'source_type' => Order::class,
            'source_id' => 1,
            'amount_collected' => 700,
            'courier_fee_share' => 100,
            'amount_owed_to_platform' => 600,
            'is_settled' => false,
            'collected_at' => now(),
        ]);

        $this->assertTrue($this->cashService->isOverCashLimit($courier));
    }

    public function test_cash_limit_not_exceeded(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'courier_max_cash_hold_limit'],
            ['value' => '2000.00', 'group' => 'settlement']
        );

        $courier = $this->createCourier();

        CourierCashCollection::create([
            'courier_id' => $courier->id,
            'source_type' => Order::class,
            'source_id' => 1,
            'amount_collected' => 100,
            'courier_fee_share' => 10,
            'amount_owed_to_platform' => 90,
            'is_settled' => false,
            'collected_at' => now(),
        ]);

        $this->assertFalse($this->cashService->isOverCashLimit($courier));
    }

    public function test_settle_collections_marks_as_settled(): void
    {
        $courier = $this->createCourier();

        CourierCashCollection::create([
            'courier_id' => $courier->id,
            'source_type' => Order::class,
            'source_id' => 1,
            'amount_collected' => 100,
            'courier_fee_share' => 10,
            'amount_owed_to_platform' => 90,
            'is_settled' => false,
            'collected_at' => now(),
        ]);

        CourierCashCollection::create([
            'courier_id' => $courier->id,
            'source_type' => Order::class,
            'source_id' => 2,
            'amount_collected' => 200,
            'courier_fee_share' => 20,
            'amount_owed_to_platform' => 180,
            'is_settled' => false,
            'collected_at' => now(),
        ]);

        $count = $this->cashService->settleCollections($courier);

        $this->assertEquals(2, $count);
        $this->assertEquals(0.00, $this->cashService->getUnsettledTotal($courier));
    }

    private function createCourier(): Courier
    {
        $user = User::factory()->create();

        return Courier::create([
            'user_id' => $user->id,
            'national_id' => fake()->numerify('##############'),
            'vehicle_type' => 'motorcycle',
            'plate_number' => 'TEST-'.fake()->randomNumber(3),
            'is_online' => true,
        ]);
    }

    /**
     * @return array{0: Order, 1: Delivery}
     */
    private function createDeliveredOrder(float $totalProducts, float $deliveryFee, float $courierFee): array
    {
        $user = User::factory()->create();

        $courier = $this->createCourier();

        $order = Order::create([
            'order_type' => 'delivery',
            'total_products' => $totalProducts,
            'status' => 'delivered',
        ]);

        // Create an address for the delivery
        $address = UserAddress::create([
            'user_id' => $user->id,
            'formatted_address' => 'Test Address',
            'latitude' => 30.0,
            'longitude' => 31.0,
            'building_number' => '1',
            'floor_number' => '1',
            'apartment_number' => '1',
            'phone' => '01000000000',
            'type' => 'home',
        ]);

        OrderDelivery::create([
            'order_id' => $order->id,
            'address_id' => $address->id,
            'total_delivery_fee' => $deliveryFee,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'fee_share' => $courierFee,
            'status' => 'completed',
        ]);

        return [$order->fresh(), $delivery];
    }
}
