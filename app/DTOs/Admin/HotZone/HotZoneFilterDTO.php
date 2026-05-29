<?php

namespace App\DTOs\Admin\HotZone;

use Illuminate\Http\Request;

readonly class HotZoneFilterDTO
{
    public function __construct(
        public ?bool $is_active = null,
        public ?string $intensity = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            is_active: $request->has('is_active') ? $request->boolean('is_active') : null,
            intensity: $request->query('intensity') ?? null,
        );
    }
}
