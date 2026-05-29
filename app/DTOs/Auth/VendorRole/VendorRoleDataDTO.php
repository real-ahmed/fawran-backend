<?php

namespace App\DTOs\Auth\VendorRole;

use Illuminate\Http\Request;

readonly class VendorRoleDataDTO
{
    public function __construct(
        public string $name,
        public ?array $permissions = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'],
            permissions: $data['permissions'] ?? null,
        );
    }
}
