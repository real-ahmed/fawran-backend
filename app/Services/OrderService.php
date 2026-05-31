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
use App\DTOs\Courier\DeliveryPreviewDTO;
use App\Services\Courier\CourierPricingService;
use App\Services\Courier\DeliveryEstimationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected DeliveryEstimationService $estimationService,
        protected CourierPricingService $pricingService
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

    public function previewDelivery(Order $order, Courier $courier): DeliveryPreviewDTO
    {
        $routeMetrics = $this->estimationService->estimateRoute($order, $courier);
        $feeMetrics = $this->pricingService->calculateFee($order, $courier, $routeMetrics);

        return new DeliveryPreviewDTO(
            route: $routeMetrics,
            fee: $feeMetrics
        );
    }

    public function assignCourier(Order $order, int $courierId, ?float $preCalculatedFeeShare = null, ?float $preCalculatedGrossFee = null): void
    {
        $courier = Courier::with('location')->find($courierId);

        $preview = $this->previewDelivery($order, $courier);

        if ($preCalculatedFeeShare !== null && $preCalculatedGrossFee !== null) {
            $feeShare = $preCalculatedFeeShare;
            $grossFee = $preCalculatedGrossFee;
        } else {
            $feeShare = $preview->fee->feeShare;
            $grossFee = $preview->fee->grossFee;
        }

        $estimatedMinutes = $preview->route->estimatedMinutes;

        DB::transaction(function () use ($order, $courierId, $feeShare, $estimatedMinutes) {
            Delivery::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier_id' => $courierId,
                    'status' => 'heading_to_vendors',
                    'fee_share' => $feeShare,
                    'estimated_minutes' => $estimatedMinutes,
                ]
            );

            // Transition status to OutForDelivery if currently pending/processing
            if (in_array($order->status->value, [OrderStatus::Pending->value, OrderStatus::Processing->value])) {
                $this->updateStatus($order, OrderStatus::OutForDelivery->value);
            }
        });
    }
}
