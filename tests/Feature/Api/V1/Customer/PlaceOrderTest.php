<?php

namespace Tests\Feature\Api\V1\Customer;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\VendorType;
use App\Jobs\Courier\BroadcastOrderToCouriersJob;
use App\Models\Address\UserAddress;
use App\Models\Order\Order;
use App\Models\Payment\Wallet;
use App\Models\Product\VendorItem;
use App\Models\Product\VendorItemInventory;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private UserAddress $address;

    private Vendor $vendor;

    private VendorItem $vendorItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create();

        $this->address = UserAddress::create([
            'user_id' => $this->customer->id,
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'formatted_address' => 'Test Address',
            'building_number' => '12A',
            'phone' => '01000000000',
        ]);

        $this->vendor = Vendor::create([
            'owner_id' => User::factory()->create()->id,
            'name' => ['en' => 'Test Vendor'],
            'type' => VendorType::RESTAURANT->value,
            'email' => 'vendor@test.com',
            'phone' => '01200000000',
            'latitude' => 30.05,
            'longitude' => 31.24,
            'formatted_address' => 'Vendor Address',
        ]);

        $category = \App\Models\Catalog\Category::create(['name' => ['en' => 'Test'], 'type' => 'restaurant']);
        $brand = \App\Models\Catalog\Brand::create(['name' => ['en' => 'Test']]);

        $masterProduct = \App\Models\Product\MasterProduct::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => ['en' => 'Test Product'],
            'unit_type' => 'piece',
        ]);

        $this->vendorItem = VendorItem::create([
            'vendor_id' => $this->vendor->id,
            'master_product_id' => $masterProduct->id,
            'price' => 100.00,
            'is_available' => true,
        ]);

        VendorItemInventory::create([
            'vendor_item_id' => $this->vendorItem->id,
            'current_stock' => 50,
            'low_stock_threshold' => 5,
        ]);

        Wallet::create([
            'user_id' => $this->customer->id,
            'balance' => 500.00,
        ]);
    }

    public function test_customer_can_place_delivery_order_with_cod()
    {
        Queue::fake();

        $response = $this->actingAs($this->customer, 'api')
            ->postJson('/api/v1/customer/orders', [
                'order_type' => OrderType::Delivery->value,
                'payment_method' => PaymentMethod::Cod->value,
                'address_id' => $this->address->id,
                'items' => [
                    [
                        'vendor_item_id' => $this->vendorItem->id,
                        'quantity' => 2,
                    ],
                ],
                'notes' => 'Leave at door',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending');

        $orderId = $response->json('data.order_id');
        $order = Order::find($orderId);

        $this->assertNotNull($order);
        $this->assertEquals(OrderType::Delivery->value, $order->order_type->value);
        $this->assertEquals(200.00, $order->total_products); // 2 * 100

        // Assert DB records
        $this->assertDatabaseHas('order_customers', ['order_id' => $orderId, 'customer_id' => $this->customer->id]);
        $this->assertDatabaseHas('order_deliveries', ['order_id' => $orderId, 'address_id' => $this->address->id]);
        $this->assertDatabaseHas('sub_orders', ['order_id' => $orderId, 'vendor_id' => $this->vendor->id]);
        $this->assertDatabaseHas('order_items', ['vendor_item_id' => $this->vendorItem->id, 'quantity' => 2]);
        $this->assertDatabaseHas('payments', ['order_id' => $orderId, 'payment_method' => 'cod', 'status' => 'pending']);

        // Assert Stock Deduction
        $this->assertDatabaseHas('vendor_item_inventory', ['vendor_item_id' => $this->vendorItem->id, 'current_stock' => 48]);
        $this->assertDatabaseHas('stock_movements', [
            'vendor_item_id' => $this->vendorItem->id,
            'quantity' => -2,
            'type' => 'sale',
            'reference_type' => Order::class,
            'reference_id' => $orderId,
        ]);

        // Assert Courier Broadcast
        Queue::assertPushed(BroadcastOrderToCouriersJob::class);
    }

    public function test_customer_can_place_pickup_order_with_wallet()
    {
        Queue::fake();

        $response = $this->actingAs($this->customer, 'api')
            ->postJson('/api/v1/customer/orders', [
                'order_type' => OrderType::Pickup->value,
                'payment_method' => PaymentMethod::Wallet->value,
                'items' => [
                    [
                        'vendor_item_id' => $this->vendorItem->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(201);

        $orderId = $response->json('data.order_id');
        $order = Order::find($orderId);

        $this->assertEquals(OrderType::Pickup->value, $order->order_type->value);
        $this->assertDatabaseMissing('order_deliveries', ['order_id' => $orderId]);

        // Assert Wallet Deduction
        $this->assertDatabaseHas('wallets', ['user_id' => $this->customer->id, 'balance' => 400.00]); // 500 - 100
        $this->assertDatabaseHas('payments', ['order_id' => $orderId, 'payment_method' => 'wallet', 'status' => 'successful']);

        // Assert Courier Broadcast NOT sent
        Queue::assertNotPushed(BroadcastOrderToCouriersJob::class);
    }

    public function test_fails_if_stock_insufficient()
    {
        $response = $this->actingAs($this->customer, 'api')
            ->postJson('/api/v1/customer/orders', [
                'order_type' => OrderType::InStore->value,
                'payment_method' => PaymentMethod::Cod->value,
                'items' => [
                    [
                        'vendor_item_id' => $this->vendorItem->id,
                        'quantity' => 100, // Stock is 50
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => __('messages.insufficient_stock', ['item' => "#{$this->vendorItem->id}"])]);
    }

    public function test_fails_if_address_missing_for_delivery()
    {
        $response = $this->actingAs($this->customer, 'api')
            ->postJson('/api/v1/customer/orders', [
                'order_type' => OrderType::Delivery->value,
                'payment_method' => PaymentMethod::Cod->value,
                // 'address_id' is missing
                'items' => [
                    [
                        'vendor_item_id' => $this->vendorItem->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('address_id');
    }
}
