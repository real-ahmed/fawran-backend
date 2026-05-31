<?php

namespace App\DTOs\Courier;

readonly class DeliveryPreviewDTO
{
    public function __construct(
        public RouteMetricsDTO $route,
        public DeliveryFeeDTO $fee,
    ) {}
}
