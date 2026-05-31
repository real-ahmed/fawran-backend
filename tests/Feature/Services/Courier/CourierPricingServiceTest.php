<?php

namespace Tests\Feature\Services\Courier;

use App\DTOs\Courier\RouteMetricsDTO;
use App\Enums\VehicleType;
use App\Models\Courier\Courier;
use App\Models\Geo\DeliveryZone;
use App\Models\Geo\DeliveryZoneVehicleFee;
use App\Models\Order\Order;
use App\Models\Order\OrderDelivery;
use App\Models\Platform\SystemSetting;
use App\Services\Courier\CourierPricingService;
use App\Services\Geo\DeliveryZoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CourierPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_fee_applies_distance_pricing_when_not_intra_zone(): void
    {
        // Set system settings
        SystemSetting::updateOrCreate(['key' => 'courier_base_start'], ['value' => '10.00', 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'courier_per_km'], ['value' => '2.00', 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'default_courier_commission'], ['value' => '10.00', 'type' => 'number']); // 10% platform fee

        // Mock DeliveryZoneService to return false for Intra-zone
        $zoneServiceMock = Mockery::mock(DeliveryZoneService::class);
        $zoneServiceMock->shouldReceive('isCoordinateInZone')->andReturn(false);

        $service = new CourierPricingService($zoneServiceMock);

        $order = new Order();
        $order->id = 1;
        $orderDelivery = new OrderDelivery();
        $zone = new DeliveryZone();
        $zone->id = 1;
        $orderDelivery->setRelation('deliveryZone', $zone);
        $order->setRelation('orderDelivery', $orderDelivery);

        $courier = new Courier();
        $courier->id = 1;
        $courier->vehicle_type = VehicleType::Car;

        $routeMetrics = new RouteMetricsDTO(
            distanceKm: 5.0, // 5km
            durationMinutes: 15.0,
            estimatedMinutes: 20
        );

        $feeDTO = $service->calculateFee($order, $courier, $routeMetrics);

        // Expected gross fee: 10 + (5 * 2) = 20
        // Expected fee share: 20 - (10%) = 18
        $this->assertEquals(20.0, $feeDTO->grossFee);
        $this->assertEquals(18.0, $feeDTO->feeShare);
        $this->assertFalse($feeDTO->isIntraZone);
    }
}
