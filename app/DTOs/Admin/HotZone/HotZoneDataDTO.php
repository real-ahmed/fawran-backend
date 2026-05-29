<?php

namespace App\DTOs\Admin\HotZone;

use Illuminate\Http\Request;

readonly class HotZoneDataDTO
{
    public function __construct(
        public ?float $center_latitude = null,
        public ?float $center_longitude = null,
        public ?int $radius_meters = null,
        public ?string $intensity = null,
        public ?bool $is_active = null,
        public ?string $starts_at = null,
        public ?array $name = null,
        public bool $has_name = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            center_latitude: isset($data['center_latitude']) ? (float) $data['center_latitude'] : null,
            center_longitude: isset($data['center_longitude']) ? (float) $data['center_longitude'] : null,
            radius_meters: isset($data['radius_meters']) ? (int) $data['radius_meters'] : null,
            intensity: $data['intensity'] ?? null,
            is_active: $data['is_active'] ?? null,
            starts_at: $data['starts_at'] ?? null,
            name: $data['name'] ?? null,
            has_name: array_key_exists('name', $data),
        );
    }
}
