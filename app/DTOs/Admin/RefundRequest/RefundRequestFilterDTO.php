<?php

namespace App\DTOs\Admin\RefundRequest;

use Illuminate\Http\Request;

readonly class RefundRequestFilterDTO
{
    public function __construct(
        public ?string $status = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: $request->query('status') ?? null,
        );
    }
}
