<?php

namespace App\Services\Customer;

use App\DTOs\Customer\Cart\CalculateDeliveryFeeDTO;
use App\DTOs\Customer\Order\PlaceOrderDTO;
use App\DTOs\Customer\Order\PlaceOrderItemDTO;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\SubOrderStatus;
use App\Models\Address\UserAddress;
use App\Models\Order\Order;
use App\Models\Order\OrderCustomer;
use App\Models\Order\OrderDelivery;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderItemNote;
use App\Models\Order\OrderItemOption;
use App\Models\Order\SubOrder;
use App\Models\Product\ProductOptionValue;
use App\Models\Product\VendorItem;
use App\Services\Geo\DeliveryZoneService;
use Illuminate\Database\Eloquent\Collection;

class OrderFactory
{
    public function __construct(
        private DeliveryZoneService $deliveryZoneService,
        private CartService $cartService
    ) {}

    /**
     * Build all order-related database records inside the caller's transaction.
     *
     * @param  Collection<VendorItem>  $vendorItems  keyed by id
     * @param  array<int, array<PlaceOrderItemDTO>>  $groupedItems  grouped by vendor_id
     */
    public function buildOrder(PlaceOrderDTO $dto, Collection $vendorItems, array $groupedItems): Order
    {
        $totalProducts = $this->calculateTotal($dto->items, $vendorItems);

        $order = Order::create([
            'order_type' => $dto->orderType->value,
            'total_products' => $totalProducts,
            'status' => OrderStatus::Pending->value,
        ]);

        OrderCustomer::create([
            'order_id' => $order->id,
            'customer_id' => $dto->customerId,
        ]);

        if ($dto->orderType === OrderType::Delivery && $dto->addressId !== null) {
            $this->createDeliveryRecord($order, $dto);
        }

        $this->createSubOrdersAndItems($order, $groupedItems, $vendorItems);

        return $order;
    }

    /**
     * Calculate the total price of all items (unit_price * quantity + option prices).
     *
     * @param  array<PlaceOrderItemDTO>  $items
     * @param  Collection<VendorItem>  $vendorItems
     */
    private function calculateTotal(array $items, Collection $vendorItems): float
    {
        $total = 0;

        foreach ($items as $itemDto) {
            $vendorItem = $vendorItems->find($itemDto->vendorItemId);
            $unitPrice = (float) $vendorItem->price;

            $optionsPrice = 0;
            if (! empty($itemDto->optionValueIds)) {
                $optionsPrice = (float) ProductOptionValue::whereIn('id', $itemDto->optionValueIds)->sum('additional_price');
            }

            $total += ($unitPrice + $optionsPrice) * $itemDto->quantity;
        }

        return round($total, 2);
    }

    private function createDeliveryRecord(Order $order, PlaceOrderDTO $dto): void
    {
        $address = UserAddress::findOrFail($dto->addressId);
        $zone = $this->deliveryZoneService->findZoneByCoordinates((float) $address->latitude, (float) $address->longitude);

        $vendorIds = array_map(fn (PlaceOrderItemDTO $item) => $item->vendorItemId, $dto->items);
        $deliveryFeeData = $this->cartService->calculateDeliveryFee(
            new CalculateDeliveryFeeDTO(
                latitude: (float) $address->latitude,
                longitude: (float) $address->longitude,
                vendorIds: VendorItem::whereIn('id', $vendorIds)->pluck('vendor_id')->unique()->toArray()
            )
        );

        OrderDelivery::create([
            'order_id' => $order->id,
            'address_id' => $dto->addressId,
            'delivery_zone_id' => $zone?->id,
            'total_delivery_fee' => $deliveryFeeData['total_delivery_fee'],
        ]);
    }

    /**
     * @param  array<int, array<PlaceOrderItemDTO>>  $groupedItems  keyed by vendor_id
     * @param  Collection<VendorItem>  $vendorItems
     */
    private function createSubOrdersAndItems(Order $order, array $groupedItems, Collection $vendorItems): void
    {
        foreach ($groupedItems as $vendorId => $itemDtos) {
            $subTotal = 0;

            foreach ($itemDtos as $itemDto) {
                $vendorItem = $vendorItems->find($itemDto->vendorItemId);
                $unitPrice = (float) $vendorItem->price;

                $optionsPrice = 0;
                if (! empty($itemDto->optionValueIds)) {
                    $optionsPrice = (float) ProductOptionValue::whereIn('id', $itemDto->optionValueIds)->sum('additional_price');
                }

                $subTotal += ($unitPrice + $optionsPrice) * $itemDto->quantity;
            }

            $subOrder = SubOrder::create([
                'order_id' => $order->id,
                'vendor_id' => $vendorId,
                'sub_total' => round($subTotal, 2),
                'status' => SubOrderStatus::Pending->value,
            ]);

            foreach ($itemDtos as $itemDto) {
                $vendorItem = $vendorItems->find($itemDto->vendorItemId);
                $unitPrice = (float) $vendorItem->price;

                $optionsPrice = 0;
                if (! empty($itemDto->optionValueIds)) {
                    $optionsPrice = (float) ProductOptionValue::whereIn('id', $itemDto->optionValueIds)->sum('additional_price');
                }

                $orderItem = OrderItem::create([
                    'sub_order_id' => $subOrder->id,
                    'vendor_item_id' => $itemDto->vendorItemId,
                    'quantity' => $itemDto->quantity,
                    'unit_price' => $unitPrice,
                    'options_price' => $optionsPrice,
                ]);

                if ($itemDto->notes !== null) {
                    OrderItemNote::create([
                        'order_item_id' => $orderItem->id,
                        'notes' => $itemDto->notes,
                    ]);
                }

                foreach ($itemDto->optionValueIds as $optionValueId) {
                    $optionValue = ProductOptionValue::find($optionValueId);
                    if ($optionValue) {
                        OrderItemOption::create([
                            'order_item_id' => $orderItem->id,
                            'product_option_id' => $optionValue->product_option_id,
                            'product_option_value_id' => $optionValueId,
                            'additional_price' => $optionValue->additional_price,
                        ]);
                    }
                }
            }
        }
    }
}
