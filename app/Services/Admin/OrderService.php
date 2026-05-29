<?php

namespace App\Services\Admin;

use App\DTOs\Admin\Order\OrderFilterDTO;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusTransition;
use App\Models\Order\Delivery;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusLog;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    use Paginatable;

    public function listOrders(OrderFilterDTO $filters)
    {
        return Order::query()
            ->withListRelations()
            ->forAdminZones()
            ->status($filters->status)
            ->type($filters->order_type)
            ->dateFrom($filters->date_from)
            ->dateTo($filters->date_to)
            ->forCustomer($filters->customer_id)
            ->forVendor($filters->vendor_id)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function getOrder(Order $order): Order
    {
        return $order->load([
            'customer.customer',
            'orderDelivery.address',
            'orderDelivery.deliveryZone',
            'subOrders.vendor',
            'subOrders.items.storeItem.masterProduct',
            'subOrders.items.options.productOption',
            'subOrders.items.options.productOptionValue',
            'subOrders.items.note',
            'delivery.courier.user',
            'delivery.courier.location',
            'payments',
            'commissions',
            'statusLogs.changedBy',
        ]);
    }

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

    public function getOrderStatusCounts(OrderFilterDTO $filters): array
    {
        $query = Order::query()->forAdminZones();

        if ($filters->date_from) {
            $query->dateFrom($filters->date_from);
        }
        if ($filters->date_to) {
            $query->dateTo($filters->date_to);
        }

        $counts = $query->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'all' => array_sum($counts),
            'pending' => $counts[OrderStatus::Pending->value] ?? 0,
            'processing' => $counts[OrderStatus::Processing->value] ?? 0,
            'out_for_delivery' => $counts[OrderStatus::OutForDelivery->value] ?? 0,
            'delivered' => $counts[OrderStatus::Delivered->value] ?? 0,
            'cancelled' => $counts[OrderStatus::Cancelled->value] ?? 0,
        ];
    }
}
