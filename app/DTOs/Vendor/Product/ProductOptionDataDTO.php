<?php

namespace App\DTOs\Vendor\Product;

class ProductOptionDataDTO
{
    /**
     * @param  ProductOptionValueDataDTO[]  $values
     */
    public function __construct(
        public array $name = [],
        public bool $is_required = false,
        public int $max_selections = 1,
        public array $values = []
    ) {}

    public static function fromValidated(array $data): self
    {
        $values = [];
        if (isset($data['values']) && is_array($data['values'])) {
            foreach ($data['values'] as $valueData) {
                $values[] = ProductOptionValueDataDTO::fromValidated($valueData);
            }
        }

        return new self(
            name: $data['name'] ?? [],
            is_required: $data['is_required'] ?? false,
            max_selections: $data['max_selections'] ?? 1,
            values: $values
        );
    }
}
