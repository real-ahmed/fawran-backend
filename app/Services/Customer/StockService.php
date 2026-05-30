<?php

namespace App\Services\Customer;

use App\Enums\StockMovementType;
use App\Models\Inventory\StockMovement;
use App\Models\Order\Order;
use App\Models\Product\VendorItemInventory;

class StockService
{
    /**
     * Deduct stock for all items in the order and create stock movement records.
     */
    public function deductForOrder(Order $order): void
    {
        $order->loadMissing('subOrders.items');

        foreach ($order->subOrders as $subOrder) {
            foreach ($subOrder->items as $orderItem) {
                VendorItemInventory::where('vendor_item_id', $orderItem->vendor_item_id)
                    ->decrement('current_stock', (float) $orderItem->quantity);

                StockMovement::create([
                    'vendor_id' => $subOrder->vendor_id,
                    'vendor_item_id' => $orderItem->vendor_item_id,
                    'quantity' => -abs((float) $orderItem->quantity),
                    'type' => StockMovementType::Sale->value,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                ]);
            }
        }
    }

    /**
     * Restore stock for all items when an order is cancelled.
     */
    public function restoreForOrder(Order $order): void
    {
        $order->loadMissing('subOrders.items');

        foreach ($order->subOrders as $subOrder) {
            foreach ($subOrder->items as $orderItem) {
                VendorItemInventory::where('vendor_item_id', $orderItem->vendor_item_id)
                    ->increment('current_stock', (float) $orderItem->quantity);

                StockMovement::create([
                    'vendor_id' => $subOrder->vendor_id,
                    'vendor_item_id' => $orderItem->vendor_item_id,
                    'quantity' => abs((float) $orderItem->quantity),
                    'type' => StockMovementType::Return->value,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                ]);
            }
        }
    }
}
