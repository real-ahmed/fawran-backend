<?php

namespace App\DTOs\Auth\VendorRole;

use Illuminate\Http\Request;

readonly class VendorRoleFilterDTO
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
