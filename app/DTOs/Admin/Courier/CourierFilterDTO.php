<?php

namespace App\DTOs\Admin\Courier;

use Illuminate\Http\Request;

readonly class CourierFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?bool $is_online = null,
        public ?string $vehicle_type = null,
        public ?int $delivery_zone_id = null,
        public ?string $approval_status = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->query('search') ?? null,
            is_online: $request->has('is_online') ? $request->boolean('is_online') : null,
            vehicle_type: $request->query('vehicle_type') ?? null,
            delivery_zone_id: $request->query('delivery_zone_id') !== null ? (int) $request->query('delivery_zone_id') : null,
            approval_status: $request->query('approval_status') ?? null,
        );
    }
}
