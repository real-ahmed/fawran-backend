<?php

namespace App\DTOs\Geo\DeliveryZone;

use Illuminate\Http\Request;

readonly class DeliveryZoneDataDTO
{
    public function __construct(
        public ?array $name = null,
        public ?bool $is_active = null,
        public ?array $coordinates = null,
        public ?array $vehicle_fees = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'] ?? null,
            is_active: $data['is_active'] ?? null,
            coordinates: $data['coordinates'] ?? null,
            vehicle_fees: $data['vehicle_fees'] ?? null,
        );
    }
}
