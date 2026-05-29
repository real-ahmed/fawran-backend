<?php

namespace App\DTOs\Auth\Role;

use Illuminate\Http\Request;

readonly class RoleDataDTO
{
    public function __construct(
        public array $display_name,
        public ?array $permissions = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            display_name: $data['display_name'] ?? [],
            permissions: $data['permissions'] ?? null,
        );
    }
}
