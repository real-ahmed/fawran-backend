<?php

namespace App\Services\Vendor;

use App\DTOs\Vendor\Order\UpdateSubOrderStatusDTO;
use App\Models\Order\SubOrder;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function listOrders(int $vendorId, array $filters = []): LengthAwarePaginator
    {
        $query = SubOrder::where('vendor_id', $vendorId)
            ->with($this->orderRelations());

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getOrderCounts(int $vendorId): array
    {
        return SubOrder::where('vendor_id', $vendorId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getOrder(SubOrder $subOrder, int $vendorId): SubOrder
    {
        $this->ensureBelongsToVendor($subOrder, $vendorId);

        return $subOrder->load($this->orderRelations());
    }

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

    private function ensureBelongsToVendor(SubOrder $subOrder, int $vendorId): void
    {
        abort_unless((int) $subOrder->vendor_id === $vendorId, 403, 'Unauthorized action.');
    }

    /**
     * @return array<int, string>
     */
    private function orderRelations(): array
    {
        return [
            'order.customer.customer',
            'items.vendorItem.masterProduct',
            'items.note',
            'items.options.productOption',
            'items.options.productOptionValue',
        ];
    }
}
