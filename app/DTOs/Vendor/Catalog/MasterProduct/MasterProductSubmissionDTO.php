<?php

namespace App\DTOs\Vendor\Catalog\MasterProduct;

use Illuminate\Http\Request;

readonly class MasterProductSubmissionDTO
{
    public function __construct(
        public int $category_id,
        public array $name,
        public string $unit_type,
        public ?array $description = null,
        public ?int $brand_id = null,
        public ?string $sku_barcode = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            category_id: $data['category_id'],
            name: $data['name'],
            unit_type: $data['unit_type'],
            description: $data['description'] ?? null,
            brand_id: $data['brand_id'] ?? null,
            sku_barcode: $data['sku_barcode'] ?? null,
        );
    }
}
