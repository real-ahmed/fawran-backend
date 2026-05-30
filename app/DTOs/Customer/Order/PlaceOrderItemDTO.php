<?php

namespace App\DTOs\Customer\Order;

class PlaceOrderItemDTO
{
    public function __construct(
        public readonly int $vendorItemId,
        public readonly float $quantity,
        public readonly ?string $notes,
        public readonly array $optionValueIds
    ) {}

    /**
     * @param  array{vendor_item_id: int, quantity: float, notes: ?string, option_value_ids: ?array<int>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            vendorItemId: (int) $data['vendor_item_id'],
            quantity: (float) $data['quantity'],
            notes: $data['notes'] ?? null,
            optionValueIds: $data['option_value_ids'] ?? []
        );
    }
}
