<?php

namespace App\DTOs\Vendor\Product;

class VendorItemDataDTO
{
    public function __construct(
        public ?int $master_product_id = null,
        public ?float $price = null,
        public ?bool $is_available = null,
        public array $extras = []
    ) {}

    public static function fromValidated(array $data): self
    {
        $extras = $data;
        unset($extras['master_product_id'], $extras['price'], $extras['is_available']);

        return new self(
            master_product_id: $data['master_product_id'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            is_available: $data['is_available'] ?? null,
            extras: $extras
        );
    }
}
