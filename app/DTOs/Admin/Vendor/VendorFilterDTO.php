<?php

namespace App\DTOs\Admin\Vendor;

use Illuminate\Http\Request;

readonly class VendorFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?bool $is_active = null,
        public ?string $type = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search') ?? null,
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            type: $request->query('type') ?? null,
        );
    }
}
