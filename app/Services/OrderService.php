<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderStatusTransition;
use App\Events\OrderConfirmed;
use App\Events\OrderDelivered;
use App\Models\Courier\Courier;
use App\Models\Order\Delivery;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusLog;
use App\Models\Platform\SystemSetting;
use App\Services\Geo\DeliveryZoneService;
use App\Services\Geo\GoogleMapsService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected GoogleMapsService $googleMapsService,
        protected DeliveryZoneService $deliveryZoneService
    ) {}

    public function cancelOrder(Order $order): Order
    {
        return $this->updateStatus($order, OrderStatus::Cancelled->value);
    }

    public function updateStatus(Order $order, string $newStatus): Order
    {
        $oldStatus = $order->status->value;

        if (! OrderStatusTransition::canTransition($oldStatus, $newStatus)) {
            throw new InvalidArgumentException("Cannot transition order from {$oldStatus} to {$newStatus}");
        }

        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            $order->update(['status' => $newStatus]);

            if ($newStatus === OrderStatus::Cancelled->value) {
                $order->subOrders()->update(['status' => 'cancelled']);
            }

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by_type' => auth()->check() ? get_class(auth()->user()) : null,
                'changed_by_id' => auth()->id(),
            ]);
        });

        // Dispatch financial events after the DB transaction commits
        if ($newStatus === OrderStatus::Processing->value) {
            OrderConfirmed::dispatch($order);
        }

        if ($newStatus === OrderStatus::Delivered->value) {
            $order->loadMissing('delivery');
            if ($order->delivery) {
                OrderDelivered::dispatch($order, $order->delivery);
            }
        }

        return $order;
    }

    public function calculateCourierFeeAndDistance(Order $order, Courier $courier): array
    {
        $order->loadMissing(['subOrders.vendor', 'orderDelivery.address']);

        $totalDistanceKm = 0;

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

            $totalDistanceKm = $this->googleMapsService->calculateRouteDistance($origin, $destination, $waypoints);
        }

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
            $grossFee = $baseStart + ($totalDistanceKm * $ratePerKm);
        }

        $platformPercentage = (float) SystemSetting::cachedValue('default_courier_commission', '5.00');
        $feeShare = round($grossFee * (1 - ($platformPercentage / 100)), 2);

        return [
            'distance_km' => $totalDistanceKm,
            'gross_fee' => round($grossFee, 2),
            'fee_share' => $feeShare,
        ];
    }

    public function assignCourier(Order $order, int $courierId, ?float $preCalculatedFeeShare = null, ?float $preCalculatedGrossFee = null): void
    {
        $courier = Courier::with('location')->find($courierId);

        if ($preCalculatedFeeShare !== null && $preCalculatedGrossFee !== null) {
            $feeShare = $preCalculatedFeeShare;
            $grossFee = $preCalculatedGrossFee;
        } else {
            $calculation = $this->calculateCourierFeeAndDistance($order, $courier);
            $feeShare = $calculation['fee_share'];
            $grossFee = $calculation['gross_fee'];
        }

        DB::transaction(function () use ($order, $courierId, $feeShare) {
            Delivery::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier_id' => $courierId,
                    'status' => 'heading_to_vendors',
                    'fee_share' => $feeShare,
                ]
            );

            // Transition status to OutForDelivery if currently pending/processing
            if (in_array($order->status->value, [OrderStatus::Pending->value, OrderStatus::Processing->value])) {
                $this->updateStatus($order, OrderStatus::OutForDelivery->value);
            }
        });
    }
}
