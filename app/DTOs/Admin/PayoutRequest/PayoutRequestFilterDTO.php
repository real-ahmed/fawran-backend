<?php

namespace App\DTOs\Admin\PayoutRequest;

use Illuminate\Http\Request;

readonly class PayoutRequestFilterDTO
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
