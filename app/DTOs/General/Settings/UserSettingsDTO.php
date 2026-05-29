<?php

namespace App\DTOs\General\Settings;

use Illuminate\Http\Request;

readonly class UserSettingsDTO
{
    public function __construct(
        public array $settings,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            settings: $request->validated('settings') ?? [],
        );
    }
}
