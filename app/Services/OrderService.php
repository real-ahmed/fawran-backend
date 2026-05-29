<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderStatusTransition;
use App\Events\OrderConfirmed;
use App\Events\OrderDelivered;
use App\Models\Order\Delivery;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusLog;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
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

    public function assignCourier(Order $order, int $courierId): void
    {
        DB::transaction(function () use ($order, $courierId) {
            Delivery::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier_id' => $courierId,
                    'status' => 'heading_to_vendors',
                    'fee_share' => 0, // Should be calculated based on settings
                ]
            );

            // Transition status to OutForDelivery if currently pending/processing
            if (in_array($order->status->value, [OrderStatus::Pending->value, OrderStatus::Processing->value])) {
                $this->updateStatus($order, OrderStatus::OutForDelivery->value);
            }
        });
    }
}
