<?php

namespace App\DTOs\Vendor\Product;

class UpdateVendorItemStatusDTO
{
    public function __construct(
        public bool $is_available
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            is_available: $data['is_available']
        );
    }
}
