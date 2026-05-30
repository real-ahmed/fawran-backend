<?php

namespace App\Services\Vendor;

use App\DTOs\Vendor\Order\UpdateSubOrderStatusDTO;
use App\Models\Order\SubOrder;
use InvalidArgumentException;

class VendorOrderService
{
    /**
     * Valid status transitions for vendor sub-orders.
     * Accepting an order means the vendor starts preparing it immediately.
     *
     * @var array<string, array<string>>
     */
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['preparing'],
        'preparing' => ['ready_for_pickup'],
        'ready_for_pickup' => [],
        'picked_up' => [],
    ];

    /**
     * Update the status of a sub-order belonging to a specific vendor.
     *
     * @throws InvalidArgumentException
     */
    public function updateSubOrderStatus(UpdateSubOrderStatusDTO $dto): SubOrder
    {
        $subOrder = SubOrder::where('id', $dto->subOrderId)
            ->where('vendor_id', $dto->vendorId)
            ->firstOrFail();

        $currentStatus = $subOrder->status->value;
        $newStatus = $dto->status->value;

        if (! $this->canTransition($currentStatus, $newStatus)) {
            throw new InvalidArgumentException(
                __('messages.invalid_sub_order_transition', [
                    'from' => $currentStatus,
                    'to' => $newStatus,
                ])
            );
        }

        $subOrder->update(['status' => $newStatus]);

        return $subOrder->refresh();
    }

    private function canTransition(string $from, string $to): bool
    {
        return isset(self::ALLOWED_TRANSITIONS[$from])
            && in_array($to, self::ALLOWED_TRANSITIONS[$from]);
    }
}
