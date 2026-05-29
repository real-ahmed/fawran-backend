<?php

namespace App\DTOs\Admin\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class MasterProductDataDTO
{
    public function __construct(
        public ?array $name = null,
        public ?array $description = null,
        public ?int $category_id = null,
        public ?string $unit_type = null,
        public ?int $brand_id = null,
        public bool $has_brand_id = false,
        public ?string $sku_barcode = null,
        public bool $has_sku_barcode = false,
        public ?bool $is_active = null,
        public ?UploadedFile $image = null,
        public bool $has_image = false,
        public ?array $images = null,
        public bool $has_images = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            category_id: $data['category_id'] ?? null,
            unit_type: $data['unit_type'] ?? null,
            brand_id: $data['brand_id'] ?? null,
            has_brand_id: array_key_exists('brand_id', $data),
            sku_barcode: $data['sku_barcode'] ?? null,
            has_sku_barcode: array_key_exists('sku_barcode', $data),
            is_active: $data['is_active'] ?? null,
            image: $data['image'] ?? null,
            has_image: array_key_exists('image', $data),
            images: $data['images'] ?? null,
            has_images: array_key_exists('images', $data),
        );
    }
}
