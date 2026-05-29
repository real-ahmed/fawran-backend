<?php

namespace App\DTOs\Admin\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class VendorDataDTO
{
    public function __construct(
        public ?int $owner_id = null,
        public ?array $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $formatted_address = null,
        public ?string $type = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?bool $is_active = null,
        public ?string $status = null,
        public ?UploadedFile $image = null,
        public bool $has_image = false,
        public ?array $working_hours = null,
        public bool $has_working_hours = false,
        public ?array $delivery_zones = null,
        public bool $has_delivery_zones = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            owner_id: $data['owner_id'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            formatted_address: $data['formatted_address'] ?? null,
            type: $data['type'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            is_active: $data['is_active'] ?? null,
            status: $data['status'] ?? null,
            image: $data['image'] ?? null,
            has_image: array_key_exists('image', $data),
            working_hours: $data['working_hours'] ?? null,
            has_working_hours: array_key_exists('working_hours', $data),
            delivery_zones: $data['delivery_zones'] ?? null,
            has_delivery_zones: array_key_exists('delivery_zones', $data),
        );
    }
}
