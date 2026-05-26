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
        $query = Order::with(['customer.customer', 'subOrders.vendor']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->query('order_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        if ($request->filled('customer_id')) {
            $query->whereHas('customer', fn ($q) => $q->where('customer_id', $request->query('customer_id')));
        }

        if ($request->filled('vendor_id')) {
            $query->whereHas('subOrders', fn ($q) => $q->where('vendor_id', $request->query('vendor_id')));
        }

        return $query->latest()->paginate($this->getPerPageLimit());
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
