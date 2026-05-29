<?php

namespace App\DTOs\Vendor\Catalog\Category;

use Illuminate\Http\Request;

readonly class CategorySubmissionDTO
{
    public function __construct(
        public array $name,
        public ?int $parent_category_id = null,
        public ?string $icon_class = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'] ?? [],
            parent_category_id: $data['parent_category_id'] ?? null,
            icon_class: $data['icon_class'] ?? null,
        );
    }
}
