<?php

namespace App\DTOs\Admin\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class BrandDataDTO
{
    public function __construct(
        public ?array $name = null,
        public ?bool $is_active = null,
        public ?UploadedFile $image = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'] ?? null,
            is_active: $data['is_active'] ?? null,
            image: $data['image'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'is_active' => $this->is_active,
            'image' => $this->image,
        ], fn ($value) => ! is_null($value));
    }
}
