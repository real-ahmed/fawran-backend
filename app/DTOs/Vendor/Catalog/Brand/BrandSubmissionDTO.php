<?php

namespace App\DTOs\Vendor\Catalog\Brand;

use Illuminate\Http\Request;

readonly class BrandSubmissionDTO
{
    public function __construct(
        public array $name,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'] ?? [],
        );
    }
}
