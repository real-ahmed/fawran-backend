<?php

namespace App\DTOs\Admin\Settlement;

use Illuminate\Http\Request;

readonly class SettlementFilterDTO
{
    public function __construct(
        public ?string $settlement_type = null,
        public ?string $status = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            settlement_type: $request->query('settlement_type') ?? null,
            status: $request->query('status') ?? null,
        );
    }
}
