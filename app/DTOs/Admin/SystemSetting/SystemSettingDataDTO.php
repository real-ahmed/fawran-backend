<?php

namespace App\DTOs\Admin\SystemSetting;

use Illuminate\Http\Request;

readonly class SystemSettingDataDTO
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
