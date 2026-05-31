<?php

namespace App\Services\Courier;

use App\DTOs\Courier\DeliveryFeeDTO;
use App\DTOs\Courier\RouteMetricsDTO;
use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Models\Platform\SystemSetting;
use App\Services\Geo\DeliveryZoneService;

class CourierPricingService
{
    public function __construct(
        protected DeliveryZoneService $deliveryZoneService
    ) {}

    public function calculateFee(Order $order, Courier $courier, RouteMetricsDTO $routeMetrics): DeliveryFeeDTO
    {
        $order->loadMissing(['subOrders.vendor', 'orderDelivery.address']);

        $zone = $order->orderDelivery?->deliveryZone;
        $vehicleFee = null;
        $isIntraZone = false;

        if ($zone) {
            // Check if courier's start, all vendors, and destination are inside the SAME zone
            $allPointsInZone = true;

            // Check Courier
            if (!$courier->location || !$this->deliveryZoneService->isCoordinateInZone($zone, $courier->location->latitude, $courier->location->longitude)) {
                $allPointsInZone = false;
            }

            // Check Destination
            if ($allPointsInZone && $order->orderDelivery?->address) {
                if (!$this->deliveryZoneService->isCoordinateInZone($zone, $order->orderDelivery->address->latitude, $order->orderDelivery->address->longitude)) {
                    $allPointsInZone = false;
                }
            } else {
                $allPointsInZone = false;
            }

            // Check Vendors
            if ($allPointsInZone) {
                foreach ($order->subOrders as $subOrder) {
                    if (!$subOrder->vendor || !$subOrder->vendor->latitude || !$subOrder->vendor->longitude || !$this->deliveryZoneService->isCoordinateInZone($zone, $subOrder->vendor->latitude, $subOrder->vendor->longitude)) {
                        $allPointsInZone = false;
                        break;
                    }
                }
            }

            $isIntraZone = $allPointsInZone;
            $vehicleFee = $zone->vehicleFees()->where('vehicle_type', $courier->vehicle_type->value)->first();
        }

        $baseStart = (float) ($vehicleFee?->base_delivery_fee ?? SystemSetting::cachedValue('courier_base_start', '0.00'));
        $ratePerKm = (float) ($vehicleFee?->fee_per_km ?? SystemSetting::cachedValue('courier_per_km', '0.00'));
        $intraZoneFlatFee = $vehicleFee?->intra_zone_flat_fee;

        if ($isIntraZone && $intraZoneFlatFee !== null) {
            $grossFee = (float) $intraZoneFlatFee;
        } else {
            $grossFee = $baseStart + ($routeMetrics->distanceKm * $ratePerKm);
        }

        $platformPercentage = (float) SystemSetting::cachedValue('default_courier_commission', '5.00');
        $feeShare = round($grossFee * (1 - ($platformPercentage / 100)), 2);

        return new DeliveryFeeDTO(
            grossFee: round($grossFee, 2),
            feeShare: $feeShare,
            isIntraZone: $isIntraZone
        );
    }
}
