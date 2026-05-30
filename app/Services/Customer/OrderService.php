<?php

namespace App\Services\Customer;

use App\DTOs\Customer\Order\PlaceOrderDTO;
use App\DTOs\Customer\Order\PlaceOrderItemDTO;
use App\Models\Order\Order;
use App\Models\Product\VendorItem;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderFactory $orderFactory,
        private StockService $stockService,
        private PaymentRecordService $paymentRecordService
    ) {}

    /**
     * Place a new order: validate items, create records, deduct stock, record payment.
     *
     * @throws Exception
     */
    public function placeOrder(PlaceOrderDTO $dto): Order
    {
        $vendorItems = $this->loadAndValidateItems($dto);
        $groupedItems = $this->groupItemsByVendor($dto, $vendorItems);

        return DB::transaction(function () use ($dto, $vendorItems, $groupedItems) {
            $order = $this->orderFactory->buildOrder($dto, $vendorItems, $groupedItems);

            $this->stockService->deductForOrder($order);

            $totalAmount = $this->calculateTotalPayableAmount($order);
            $this->paymentRecordService->recordPayment($order, $dto->paymentMethod, $totalAmount);

            return $order->load(['customer', 'subOrders.items', 'orderDelivery', 'payments']);
        });
    }

    /**
     * Load all vendor items from the DTO and validate they exist and are available.
     *
     * @throws Exception
     */
    private function loadAndValidateItems(PlaceOrderDTO $dto): Collection
    {
        $vendorItemIds = array_map(fn ($item) => $item->vendorItemId, $dto->items);

        $vendorItems = VendorItem::with(['inventory', 'masterProduct'])
            ->whereIn('id', $vendorItemIds)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        foreach ($dto->items as $itemDto) {
            $vendorItem = $vendorItems->get($itemDto->vendorItemId);

            if (! $vendorItem) {
                throw new Exception(__('messages.item_not_available', ['id' => $itemDto->vendorItemId]));
            }

            if ($vendorItem->inventory && (float) $vendorItem->inventory->current_stock < $itemDto->quantity) {
                $productName = is_array($vendorItem->masterProduct?->name)
                    ? ($vendorItem->masterProduct?->name['en'] ?? "#{$vendorItem->id}")
                    : ($vendorItem->masterProduct?->name ?? "#{$vendorItem->id}");

                throw new Exception(__('messages.insufficient_stock', ['item' => (string) $productName]));
            }
        }

        return $vendorItems;
    }

    /**
     * Group DTO items by their vendor ID.
     *
     * @return array<int, array<PlaceOrderItemDTO>>
     */
    private function groupItemsByVendor(PlaceOrderDTO $dto, Collection $vendorItems): array
    {
        $grouped = [];

        foreach ($dto->items as $itemDto) {
            $vendorItem = $vendorItems->get($itemDto->vendorItemId);
            $vendorId = $vendorItem->vendor_id;

            $grouped[$vendorId][] = $itemDto;
        }

        return $grouped;
    }

    /**
     * Calculate the total payable amount (products + delivery fee).
     */
    private function calculateTotalPayableAmount(Order $order): float
    {
        $order->loadMissing('orderDelivery');

        $productsTotal = (float) $order->total_products;
        $deliveryFee = (float) ($order->orderDelivery?->total_delivery_fee ?? 0);

        return round($productsTotal + $deliveryFee, 2);
    }
}
