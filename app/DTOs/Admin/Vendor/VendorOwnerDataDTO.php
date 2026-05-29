<?php

namespace App\DTOs\Admin\Vendor;

use Illuminate\Http\Request;

readonly class VendorOwnerDataDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public ?bool $is_active = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'],
            is_active: $data['is_active'] ?? null,
        );
    }
}
