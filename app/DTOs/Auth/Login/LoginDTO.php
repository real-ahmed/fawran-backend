<?php

namespace App\DTOs\Auth\Login;

use Illuminate\Http\Request;

readonly class LoginDTO
{
    public function __construct(
        public string $login,
        public string $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            login: $request->validated('login'),
            password: $request->validated('password'),
        );
    }
}
