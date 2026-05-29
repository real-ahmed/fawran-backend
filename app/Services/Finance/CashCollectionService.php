<?php

namespace App\Services\Finance;

use App\Models\Courier\Courier;
use App\Models\Order\Delivery;
use App\Models\Order\Order;
use App\Models\Payment\CourierCashCollection;
use App\Models\Platform\SystemSetting;

class CashCollectionService
{
    /**
     * Record a cash collection when a COD order is delivered.
     *
     * @param  Order  $order  The order with payment and delivery loaded
     */
    public function recordCollection(Order $order, Delivery $delivery): CourierCashCollection
    {
        $amountCollected = (float) $order->total_products
            + (float) ($order->orderDelivery?->total_delivery_fee ?? 0);

        $courierFeeShare = (float) $delivery->fee_share;
        $amountOwedToPlatform = round($amountCollected - $courierFeeShare, 2);

        return CourierCashCollection::create([
            'courier_id' => $delivery->courier_id,
            'source_type' => $order->getMorphClass(),
            'source_id' => $order->getKey(),
            'amount_collected' => $amountCollected,
            'courier_fee_share' => $courierFeeShare,
            'amount_owed_to_platform' => max($amountOwedToPlatform, 0),
            'is_settled' => false,
            'collected_at' => now(),
        ]);
    }

    /**
     * Get total unsettled cash owed to platform by a courier.
     */
    public function getUnsettledTotal(Courier $courier): float
    {
        return (float) CourierCashCollection::query()
            ->where('courier_id', $courier->id)
            ->where('is_settled', false)
            ->sum('amount_owed_to_platform');
    }

    /**
     * Check if courier has exceeded the max cash hold limit.
     */
    public function isOverCashLimit(Courier $courier): bool
    {
        $limit = (float) SystemSetting::cachedValue('courier_max_cash_hold_limit', '2000.00');

        return $this->getUnsettledTotal($courier) > $limit;
    }

    /**
     * Mark all unsettled collections for a courier as settled.
     *
     * @return int Number of records settled
     */
    public function settleCollections(Courier $courier): int
    {
        return CourierCashCollection::query()
            ->where('courier_id', $courier->id)
            ->where('is_settled', false)
            ->update(['is_settled' => true]);
    }
}
