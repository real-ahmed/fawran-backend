<?php

namespace App\DTOs\Auth\Role;

use Illuminate\Http\Request;

readonly class RoleFilterDTO
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
