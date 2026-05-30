<?php

namespace App\DTOs\Customer\Order;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;

class PlaceOrderDTO
{
    /**
     * @param  array<PlaceOrderItemDTO>  $items
     */
    public function __construct(
        public readonly int $customerId,
        public readonly OrderType $orderType,
        public readonly PaymentMethod $paymentMethod,
        public readonly ?int $addressId,
        public readonly array $items,
        public readonly ?string $notes
    ) {}

    /**
     * @param  array{order_type: string, payment_method: string, address_id: ?int, items: array, notes: ?string}  $data
     */
    public static function fromValidated(array $data, int $customerId): self
    {
        $items = array_map(
            fn (array $item) => PlaceOrderItemDTO::fromArray($item),
            $data['items']
        );

        return new self(
            customerId: $customerId,
            orderType: OrderType::from($data['order_type']),
            paymentMethod: PaymentMethod::from($data['payment_method']),
            addressId: $data['address_id'] ?? null,
            items: $items,
            notes: $data['notes'] ?? null
        );
    }
}
