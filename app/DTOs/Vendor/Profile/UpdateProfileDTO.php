<?php

namespace App\DTOs\Vendor\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class UpdateProfileDTO
{
    public function __construct(
        public ?array $name = null,
        public ?array $description = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $formatted_address = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?UploadedFile $image = null,
        public bool $has_image = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            formatted_address: $data['formatted_address'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            image: $data['image'] ?? null,
            has_image: array_key_exists('image', $data),
        );
    }
}
