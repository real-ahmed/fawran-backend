<?php

namespace App\DTOs\Courier\Order;

class CourierAcceptOrderDTO
{
    public function __construct(
        public readonly int $courierId,
        public readonly int $orderId
    ) {}

    public static function fromRequest(int $courierId, int $orderId): self
    {
        return new self($courierId, $orderId);
    }
}
