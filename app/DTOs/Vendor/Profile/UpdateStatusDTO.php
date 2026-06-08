<?php

namespace App\DTOs\Vendor\Profile;

use App\Enums\VendorStatus;
use Illuminate\Http\Request;

readonly class UpdateStatusDTO
{
    public function __construct(
        public VendorStatus $status,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            status: VendorStatus::from($data['status']),
        );
    }
}
