<?php

namespace App\DTOs\Admin;

use Illuminate\Http\Request;

readonly class AdminUserDataDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?bool $is_active = null,
        public ?array $roles = null,
        public ?array $delivery_zones = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            is_active: $data['is_active'] ?? null,
            roles: $data['roles'] ?? null,
            delivery_zones: $data['delivery_zones'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'is_active' => $this->is_active,
        ], fn ($value) => ! is_null($value));
    }
}
