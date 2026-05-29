<?php

namespace App\DTOs\Auth\Login;

use Illuminate\Http\Request;

readonly class AdminLoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->validated('email'),
            password: $request->validated('password'),
        );
    }
}
