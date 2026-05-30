<?php

namespace App\Services\Customer;

use App\DTOs\Customer\Cart\CalculateDeliveryFeeDTO;
use App\Enums\VehicleType;
use App\Models\Platform\SystemSetting;
use App\Models\Vendor\Vendor;
use App\Services\Geo\DeliveryZoneService;
use App\Services\Geo\GoogleMapsService;

class CartService
{
    public function __construct(
        private GoogleMapsService $googleMapsService,
        private DeliveryZoneService $deliveryZoneService
    ) {}

    /**
     * Calculate delivery fee based on vendor locations and customer location.
     */
    public function calculateDeliveryFee(CalculateDeliveryFeeDTO $dto): array
    {
        if (empty($dto->vendorIds)) {
            return [
                'distance_km' => 0,
                'total_delivery_fee' => 0,
            ];
        }

        $vendors = Vendor::whereIn('id', $dto->vendorIds)->get();

        if ($vendors->isEmpty()) {
            return [
                'distance_km' => 0,
                'total_delivery_fee' => 0,
            ];
        }

        // 1. Calculate Distance
        $firstVendor = $vendors->first();
        $origin = [
            'lat' => $firstVendor->latitude,
            'lng' => $firstVendor->longitude,
        ];

        $destination = [
            'lat' => $dto->latitude,
            'lng' => $dto->longitude,
        ];

        $waypoints = [];
        // Add remaining vendors as waypoints (skipping the first one)
        foreach ($vendors->slice(1) as $vendor) {
            if ($vendor->latitude && $vendor->longitude) {
                $waypoints[] = [
                    'lat' => $vendor->latitude,
                    'lng' => $vendor->longitude,
                ];
            }
        }

        $totalDistanceKm = $this->googleMapsService->calculateRouteDistance($origin, $destination, $waypoints);

        // 2. Determine Rates
        $zone = $this->deliveryZoneService->findZoneByCoordinates($dto->latitude, $dto->longitude);
        $vehicleFee = null;

        if ($zone) {
            // Defaulting to Motorcycle rate for generic customer pricing
            $vehicleFee = $zone->vehicleFees()->where('vehicle_type', VehicleType::Motorcycle->value)->first();
        }

        $baseStart = (float) ($vehicleFee?->base_delivery_fee ?? SystemSetting::cachedValue('courier_base_start', '0.00'));
        $ratePerKm = (float) ($vehicleFee?->fee_per_km ?? SystemSetting::cachedValue('courier_per_km', '0.00'));

        $totalDeliveryFee = $baseStart + ($totalDistanceKm * $ratePerKm);

        return [
            'distance_km' => $totalDistanceKm,
            'total_delivery_fee' => round($totalDeliveryFee, 2),
        ];
    }
}
