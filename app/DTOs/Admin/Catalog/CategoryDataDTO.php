<?php

namespace App\DTOs\Admin\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class CategoryDataDTO
{
    public function __construct(
        public ?array $name = null,
        public ?bool $is_active = null,
        public ?int $parent_category_id = null,
        public bool $has_parent_category_id = false,
        public ?UploadedFile $icon = null,
        public bool $has_icon = false,
        public ?UploadedFile $image = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'] ?? null,
            is_active: $data['is_active'] ?? null,
            parent_category_id: $data['parent_category_id'] ?? null,
            has_parent_category_id: array_key_exists('parent_category_id', $data),
            icon: $data['icon'] ?? null,
            has_icon: array_key_exists('icon', $data),
            image: $data['image'] ?? null,
        );
    }
}
