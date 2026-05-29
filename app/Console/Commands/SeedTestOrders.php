<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Address\UserAddress;
use App\Models\Catalog\Category;
use App\Models\Courier\Courier;
use App\Models\Courier\CourierLocation;
use App\Models\Geo\DeliveryZone;
use App\Models\Order\Delivery;
use App\Models\Order\Order;
use App\Models\Order\OrderCustomer;
use App\Models\Order\OrderDelivery;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderStatusLog;
use App\Models\Order\SubOrder;
use App\Models\Product\MasterProduct;
use App\Models\Product\VendorItem;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedTestOrders extends Command
{
    protected $signature = 'seed:test-orders';

    protected $description = 'Seed various order scenarios for frontend testing';

    public function handle()
    {
        $this->info('Starting test data generation...');

        $user = User::firstOrCreate(
            ['email' => 'vendor_owner@test.com'],
            ['name' => 'Test Vendor Owner', 'password' => bcrypt('password'), 'phone' => '01000000000', 'is_active' => true]
        );

        // 1. Ensure basic requirements
        $vendor1 = Vendor::firstOrCreate(
            ['email' => 'vendor1@test.com'],
            [
                'name' => 'Test Vendor 1', 'owner_id' => $user->id, 'status' => 'online', 'is_active' => true,
                'phone' => '01111111111', 'type' => 'restaurant', 'formatted_address' => 'Cairo',
                'latitude' => 30.0444, 'longitude' => 31.2357,
            ]
        );
        $vendor2 = Vendor::firstOrCreate(
            ['email' => 'vendor2@test.com'],
            [
                'name' => 'Test Vendor 2', 'owner_id' => $user->id, 'status' => 'online', 'is_active' => true,
                'phone' => '02222222222', 'type' => 'restaurant', 'formatted_address' => 'Cairo',
                'latitude' => 30.0500, 'longitude' => 31.2400,
            ]
        );

        $zone = DeliveryZone::firstOrCreate(
            ['name' => ['en' => 'Cairo Downtown', 'ar' => 'القاهرة']],
            ['is_active' => true, 'polygon' => DB::raw("ST_GeomFromText('POLYGON((0 0, 0 1, 1 1, 1 0, 0 0))')")]
        );

        $category = Category::firstOrCreate(
            ['name' => 'Test Category'],
            ['is_active' => true]
        );

        // Ensure VendorItems exist for strict foreign key constraints
        $masterProduct = MasterProduct::firstOrCreate(
            ['name' => 'Test Master Product'],
            ['status' => 'approved', 'is_active' => true, 'category_id' => $category->id, 'unit_type' => 'piece']
        );

        $vendorItem1 = VendorItem::firstOrCreate(
            ['vendor_id' => $vendor1->id, 'master_product_id' => $masterProduct->id],
            ['price' => 75.00, 'is_active' => true, 'in_stock' => true]
        );
        $vendorItem2 = VendorItem::firstOrCreate(
            ['vendor_id' => $vendor2->id, 'master_product_id' => $masterProduct->id],
            ['price' => 200.00, 'is_active' => true, 'in_stock' => true]
        );

        $courierUser = User::firstOrCreate(
            ['email' => 'test_courier@test.com'],
            ['name' => 'Ahmed Courier', 'password' => bcrypt('password'), 'phone' => '01222222222', 'is_active' => true]
        );

        $courier = Courier::firstOrCreate(
            ['user_id' => $courierUser->id],
            ['national_id' => '29001010101010', 'vehicle_type' => 'motorcycle', 'plate_number' => 'ABC-123', 'is_online' => true]
        );

        CourierLocation::firstOrCreate(
            ['courier_id' => $courier->id],
            ['latitude' => 30.0400, 'longitude' => 31.2300, 'located_at' => now()]
        );

        // --- SCENARIO 1: Delivery Order with Multiple Vendors ---
        $this->info('Creating Scenario 1: Delivery Order (Multiple Vendors)...');
        $order1 = Order::create([
            'order_type' => OrderType::Delivery->value,
            'total_products' => 350.00,
            'status' => OrderStatus::Pending->value,
        ]);

        OrderCustomer::create([
            'order_id' => $order1->id,
            'customer_id' => $user->id,
        ]);

        $address = UserAddress::firstOrCreate(
            ['user_id' => $user->id],
            [
                'formatted_address' => '123 Test Street',
                'latitude' => 30.0,
                'longitude' => 31.0,
                'building_number' => '15',
                'floor_number' => '1',
                'apartment_number' => '2',
                'phone' => '01001234567',
                'type' => 'home',
            ]
        );

        $delivery1 = OrderDelivery::create([
            'order_id' => $order1->id,
            'address_id' => $address->id,
            'delivery_zone_id' => $zone->id,
            'total_delivery_fee' => 35.00,
            'courier_id' => $courier->id,
        ]);

        Delivery::updateOrCreate(
            ['order_id' => $order1->id],
            ['courier_id' => $courier->id, 'status' => 'heading_to_vendors', 'fee_share' => 20.00]
        );

        // Sub-order 1 (Vendor 1)
        $sub1 = SubOrder::create([
            'order_id' => $order1->id,
            'vendor_id' => $vendor1->id,
            'sub_total' => 150.00,
            'status' => 'pending',
        ]);
        OrderItem::create([
            'sub_order_id' => $sub1->id,
            'vendor_item_id' => $vendorItem1->id,
            'quantity' => 2,
            'unit_price' => 75.00,
            'options_price' => 0.00,
            // 'notes' => 'No onions please', // notes is actually in OrderItemNote
        ]);

        // Sub-order 2 (Vendor 2)
        $sub2 = SubOrder::create([
            'order_id' => $order1->id,
            'vendor_id' => $vendor2->id,
            'sub_total' => 200.00,
            'status' => 'pending',
        ]);
        OrderItem::create([
            'sub_order_id' => $sub2->id,
            'vendor_item_id' => $vendorItem2->id,
            'quantity' => 1,
            'unit_price' => 200.00,
            'options_price' => 0.00,
        ]);

        OrderStatusLog::create([
            'order_id' => $order1->id,
            'from_status' => null,
            'to_status' => OrderStatus::Pending->value,
            'changed_by_name' => 'System',
        ]);

        // --- SCENARIO 2: In-Store Order ---
        $this->info('Creating Scenario 2: In-Store Order...');
        $order2 = Order::create([
            'order_type' => OrderType::InStore->value,
            'total_products' => 85.00,
            'status' => OrderStatus::Delivered->value, // Assume completed
        ]);

        OrderCustomer::create([
            'order_id' => $order2->id,
            'customer_id' => $user->id,
        ]);

        $sub3 = SubOrder::create([
            'order_id' => $order2->id,
            'vendor_id' => $vendor1->id,
            'sub_total' => 85.00,
            'status' => 'ready_for_pickup',
        ]);
        OrderItem::create([
            'sub_order_id' => $sub3->id,
            'vendor_item_id' => $vendorItem1->id,
            'quantity' => 1,
            'unit_price' => 85.00,
            'options_price' => 0.00,
        ]);

        OrderStatusLog::create([
            'order_id' => $order2->id,
            'from_status' => null,
            'to_status' => OrderStatus::Pending->value,
        ]);
        OrderStatusLog::create([
            'order_id' => $order2->id,
            'from_status' => OrderStatus::Pending->value,
            'to_status' => OrderStatus::Delivered->value,
        ]);

        // --- SCENARIO 3: Order Between Friends (P2P simulation) ---
        // Note: You have a separate P2P module (P2pDelivery), but to view it in the regular Order list
        // as requested, we will simulate it as a Pickup/Delivery order with generic details.
        // If you meant the actual p2p_deliveries table, that is a completely different feature/frontend screen.
        $this->info('Creating Scenario 3: P2P-style Delivery (Simulated as Order)...');
        $order3 = Order::create([
            'order_type' => OrderType::Delivery->value,
            'total_products' => 0.00, // No products, just delivery
            'status' => OrderStatus::Processing->value,
        ]);

        OrderCustomer::create([
            'order_id' => $order3->id,
            'customer_id' => $user->id,
        ]);

        $delivery3 = OrderDelivery::create([
            'order_id' => $order3->id,
            'address_id' => $address->id,
            'delivery_zone_id' => $zone->id,
            'total_delivery_fee' => 50.00,
            'courier_id' => $courier->id,
        ]);

        Delivery::updateOrCreate(
            ['order_id' => $order3->id],
            ['courier_id' => $courier->id, 'status' => 'heading_to_customer', 'fee_share' => 30.00]
        );

        // Simulating the P2P package as an item
        $sub4 = SubOrder::create([
            'order_id' => $order3->id,
            'vendor_id' => $vendor1->id, // Generic assignment
            'sub_total' => 0.00,
            'status' => 'preparing',
        ]);
        OrderItem::create([
            'sub_order_id' => $sub4->id,
            'vendor_item_id' => $vendorItem1->id,
            'quantity' => 1,
            'unit_price' => 0.00,
            'options_price' => 0.00,
        ]);

        OrderStatusLog::create([
            'order_id' => $order3->id,
            'from_status' => null,
            'to_status' => OrderStatus::Processing->value,
            'changed_by_name' => 'Admin User',
        ]);

        $this->info('Test data successfully seeded! Go to your frontend Orders page to view them.');
    }
}
