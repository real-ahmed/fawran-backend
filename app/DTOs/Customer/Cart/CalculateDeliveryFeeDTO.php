<?php

namespace App\DTOs\Customer\Cart;

use Illuminate\Http\Request;

class CalculateDeliveryFeeDTO
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly array $vendorIds
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            (float) $request->input('latitude'),
            (float) $request->input('longitude'),
            (array) $request->input('vendor_ids')
        );
    }
}
