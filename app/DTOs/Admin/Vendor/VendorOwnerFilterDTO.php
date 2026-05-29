<?php

namespace App\DTOs\Admin\Vendor;

use Illuminate\Http\Request;

readonly class VendorOwnerFilterDTO
{
    public function __construct(
        public ?string $search = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search') ?? null,
        );
    }
}
