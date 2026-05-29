<?php

namespace App\DTOs\Admin\RefundRequest;

use Illuminate\Http\Request;

readonly class RefundRequestDataDTO
{
    public function __construct(
        public string $status,
        public ?string $resolution = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            status: $data['status'],
            resolution: $data['resolution'] ?? null,
        );
    }
}
