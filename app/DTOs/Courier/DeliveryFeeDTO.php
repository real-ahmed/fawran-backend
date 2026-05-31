<?php

namespace App\DTOs\Courier;

readonly class DeliveryFeeDTO
{
    public function __construct(
        public float $grossFee,
        public float $feeShare,
        public bool $isIntraZone,
    ) {}
}
