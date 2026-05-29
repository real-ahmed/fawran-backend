<?php

namespace App\DTOs\Admin\Courier;

use Illuminate\Http\Request;

readonly class CourierDataDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $phone = null,
        public ?string $national_id = null,
        public ?string $vehicle_type = null,
        public ?string $plate_number = null,
        public bool $has_plate_number = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            national_id: $data['national_id'] ?? null,
            vehicle_type: $data['vehicle_type'] ?? null,
            plate_number: $data['plate_number'] ?? null,
            has_plate_number: array_key_exists('plate_number', $data),
        );
    }
}
