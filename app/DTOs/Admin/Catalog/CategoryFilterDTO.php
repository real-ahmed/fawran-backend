<?php

namespace App\DTOs\Admin\Catalog;

use Illuminate\Http\Request;

readonly class CategoryFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $approval_status = null,
        public ?bool $is_active = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search') ?? null,
            approval_status: $request->query('approval_status') ?? null,
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
        );
    }
}
