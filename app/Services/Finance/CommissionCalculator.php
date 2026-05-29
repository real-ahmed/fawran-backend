<?php

namespace App\Services\Finance;

use App\Models\Order\Order;
use App\Models\Platform\OrderCommission;
use App\Models\Platform\SystemSetting;

class CommissionCalculator
{
    /**
     * Calculate and freeze commissions for an order.
     *
     * Values are locked into order_commissions so future settings
     * changes don't affect past orders.
     */
    public function calculateForOrder(Order $order): void
    {
        $order->loadMissing(['subOrders.vendor.customCommission', 'delivery', 'orderDelivery']);

        $defaultCommission = (float) (SystemSetting::cachedValue('default_vendorcommission', '10.00'));
        $deliveryFeeShare = (float) ($order->delivery?->fee_share ?? 0);
        $totalDeliveryFee = (float) ($order->orderDelivery?->total_delivery_fee ?? 0);

        foreach ($order->subOrders as $subOrder) {
            // Skip if commission already exists (idempotent)
            if (OrderCommission::where('order_id', $order->id)->where('vendor_id', $subOrder->vendor_id)->exists()) {
                continue;
            }

            $commissionPercentage = (float) (
                $subOrder->vendor?->customCommission?->commission_percentage
                ?? $defaultCommission
            );

            $subTotal = (float) $subOrder->sub_total;
            $commissionAmount = round($subTotal * $commissionPercentage / 100, 2);

            // Platform delivery share = total delivery fee minus courier cut
            // Proportioned by vendor sub-total if multiple vendors
            $orderTotal = (float) $order->total_products;
            $vendorProportion = $orderTotal > 0 ? $subTotal / $orderTotal : 1;
            $appDeliveryShare = round(($totalDeliveryFee - $deliveryFeeShare) * $vendorProportion, 2);

            $netPlatformProfit = round($commissionAmount + $appDeliveryShare, 2);

            OrderCommission::create([
                'order_id' => $order->id,
                'vendor_id' => $subOrder->vendor_id,
                'vendorcommission_percentage' => $commissionPercentage,
                'vendorcommission_amount' => $commissionAmount,
                'app_delivery_share' => max($appDeliveryShare, 0),
                'net_platform_profit' => max($netPlatformProfit, 0),
            ]);
        }
    }
}
