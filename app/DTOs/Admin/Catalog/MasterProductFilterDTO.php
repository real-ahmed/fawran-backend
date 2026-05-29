<?php

namespace App\DTOs\Admin\Catalog;

use Illuminate\Http\Request;

readonly class MasterProductFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?int $category_id = null,
        public ?int $brand_id = null,
        public ?string $approval_status = null,
        public ?bool $is_active = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search') ?? null,
            category_id: $request->query('category_id') !== null ? (int) $request->query('category_id') : null,
            brand_id: $request->query('brand_id') !== null ? (int) $request->query('brand_id') : null,
            approval_status: $request->query('approval_status') ?? null,
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
        );
    }
}
