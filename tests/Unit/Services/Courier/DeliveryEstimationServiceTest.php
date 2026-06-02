<?php

namespace Tests\Unit\Services\Courier;

use App\Models\Address\UserAddress;
use App\Models\Courier\Courier;
use App\Models\Courier\CourierLocation;
use App\Models\Order\Order;
use App\Models\Order\OrderDelivery;
use App\Models\Order\OrderItem;
use App\Models\Order\SubOrder;
use App\Models\Product\RestaurantDishDetail;
use App\Models\Product\VendorItem;
use App\Models\Vendor\Vendor;
use App\Services\Courier\DeliveryEstimationService;
use App\Services\Geo\GoogleMapsService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class DeliveryEstimationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_estimate_route_calculates_correct_metrics(): void
    {
        // Mock GoogleMapsService
        $googleMapsMock = Mockery::mock(GoogleMapsService::class);
        $googleMapsMock->shouldReceive('calculateRouteMetrics')
            ->once()
            ->andReturn([
                'distance_km' => 10.5,
                'duration_minutes' => 20.0,
            ]);

        $service = new DeliveryEstimationService($googleMapsMock);

        // Setup Courier with location
        $courier = new Courier;
        $courierLocation = new CourierLocation;
        $courierLocation->latitude = 24.0;
        $courierLocation->longitude = 46.0;
        $courier->setRelation('location', $courierLocation);

        // Setup Order Delivery Address
        $order = new Order;
        $orderDelivery = new OrderDelivery;
        $address = new UserAddress;
        $address->latitude = 24.1;
        $address->longitude = 46.1;
        $orderDelivery->setRelation('address', $address);
        $order->setRelation('orderDelivery', $orderDelivery);

        // Setup Vendor & Items for Max Prep Time Calculation
        $vendor = new Vendor;
        $vendor->latitude = 24.05;
        $vendor->longitude = 46.05;

        // Vendor Item 1 (Prep time 10)
        $dish1 = new RestaurantDishDetail;
        $dish1->preparation_time = 10;
        $vendorItem1 = new VendorItem;
        $vendorItem1->setRelation('restaurantDishDetail', $dish1);
        $orderItem1 = new OrderItem;
        $orderItem1->setRelation('vendorItem', $vendorItem1);

        // Vendor Item 2 (Prep time 15)
        $dish2 = new RestaurantDishDetail;
        $dish2->preparation_time = 15;
        $vendorItem2 = new VendorItem;
        $vendorItem2->setRelation('restaurantDishDetail', $dish2);
        $orderItem2 = new OrderItem;
        $orderItem2->setRelation('vendorItem', $vendorItem2);

        $subOrder = new SubOrder;
        $subOrder->setRelation('vendor', $vendor);
        $subOrder->setRelation('items', Collection::make([$orderItem1, $orderItem2]));

        $order->setRelation('subOrders', Collection::make([$subOrder]));

        // Act
        $result = $service->estimateRoute($order, $courier);

        // Assert
        $this->assertEquals(10.5, $result->distanceKm);
        $this->assertEquals(20.0, $result->durationMinutes);
        // Estimated minutes = duration (20) + max_prep_time (15) + buffer (5) = 40
        $this->assertEquals(40, $result->estimatedMinutes);
    }
}
