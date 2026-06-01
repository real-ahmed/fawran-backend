<?php

namespace App\DTOs\Admin\Profile;

use App\Http\Requests\V1\Admin\Profile\UpdateProfileRequest;

class UpdateAdminProfileDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password
    ) {}

    public static function fromRequest(UpdateProfileRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password')
        );
    }
}
