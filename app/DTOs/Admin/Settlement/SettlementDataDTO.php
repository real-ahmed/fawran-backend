<?php

namespace App\DTOs\Admin\Settlement;

use Illuminate\Http\Request;

readonly class SettlementDataDTO
{
    public function __construct(
        public string $execution_method,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            execution_method: $data['execution_method'],
            notes: $data['notes'] ?? null,
        );
    }
}
