<?php

namespace App\Services\Courier;

use App\DTOs\Courier\RouteMetricsDTO;
use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Services\Geo\GoogleMapsService;

class DeliveryEstimationService
{
    public function __construct(
        protected GoogleMapsService $googleMapsService
    ) {}

    public function estimateRoute(Order $order, Courier $courier): RouteMetricsDTO
    {
        $order->loadMissing(['subOrders.vendor', 'subOrders.items.vendorItem.restaurantDishDetail', 'orderDelivery.address']);

        $totalDistanceKm = 0;
        $totalDurationMinutes = 0;

        if ($courier->location && $order->orderDelivery?->address) {
            $origin = [
                'lat' => $courier->location->latitude,
                'lng' => $courier->location->longitude,
            ];

            $destination = [
                'lat' => $order->orderDelivery->address->latitude,
                'lng' => $order->orderDelivery->address->longitude,
            ];

            $waypoints = [];
            foreach ($order->subOrders as $subOrder) {
                if ($subOrder->vendor && $subOrder->vendor->latitude && $subOrder->vendor->longitude) {
                    $waypoints[] = [
                        'lat' => $subOrder->vendor->latitude,
                        'lng' => $subOrder->vendor->longitude,
                    ];
                }
            }

            $metrics = $this->googleMapsService->calculateRouteMetrics($origin, $destination, $waypoints);
            $totalDistanceKm = $metrics['distance_km'];
            $totalDurationMinutes = $metrics['duration_minutes'];
        }

        // Calculate max preparation time
        $maxPrepTime = 0;
        foreach ($order->subOrders as $subOrder) {
            foreach ($subOrder->items as $item) {
                $prepTime = $item->vendorItem?->restaurantDishDetail?->preparation_time ?? 0;
                if ($prepTime > $maxPrepTime) {
                    $maxPrepTime = $prepTime;
                }
            }
        }

        // Add 5 minutes buffer
        $estimatedMinutes = (int) ceil($totalDurationMinutes + $maxPrepTime + 5);

        return new RouteMetricsDTO(
            distanceKm: $totalDistanceKm,
            durationMinutes: $totalDurationMinutes,
            estimatedMinutes: $estimatedMinutes
        );
    }
}
