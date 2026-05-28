<?php

namespace App\Services\Admin;

use App\Models\Order\Order;
use App\Traits\Paginatable;
use Illuminate\Http\Request;

class OrderService
{
    use Paginatable;

    public function listOrders(Request $request)
    {
        return Order::query()
            ->withListRelations()
            ->forAdminZones()
            ->status($request->query('status'))
            ->type($request->query('order_type'))
            ->dateFrom($request->query('date_from'))
            ->dateTo($request->query('date_to'))
            ->forCustomer($request->query('customer_id'))
            ->forVendor($request->query('vendor_id'))
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function getOrder(Order $order): Order
    {
        return $order->load([
            'customer.customer',
            'orderDelivery.deliveryZone',
            'subOrders.vendor',
            'subOrders.items.storeItem',
            'subOrders.items.options',
            'delivery.courier.user',
            'payments',
            'commissions',
        ]);
    }

    public function cancelOrder(Order $order): Order
    {
        $order->update(['status' => 'cancelled']);

        $order->subOrders()->update(['status' => 'cancelled']);

        return $order;
    }
}
