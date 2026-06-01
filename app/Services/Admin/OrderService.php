<?php

namespace App\Services\Admin;

use App\DTOs\Admin\Order\OrderFilterDTO;
use App\Enums\OrderStatus;
use App\Models\Order\Order;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;

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
            ->forCourier($filters->courier_id)
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
            'subOrders.items.vendorItem.masterProduct',
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
