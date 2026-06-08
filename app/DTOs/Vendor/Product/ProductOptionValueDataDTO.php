<?php

namespace App\DTOs\Vendor\Product;

class ProductOptionValueDataDTO
{
    public function __construct(
        public ?int $id = null,
        public array $name = [],
        public float $additional_price = 0.00,
        public bool $is_available = true,
        public bool $is_deleted = false
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? [],
            additional_price: isset($data['additional_price']) ? (float) $data['additional_price'] : 0.00,
            is_available: $data['is_available'] ?? true,
            is_deleted: $data['is_deleted'] ?? false
        );
    }
}
