<?php

namespace App\DTOs\Courier;

readonly class RouteMetricsDTO
{
    public function __construct(
        public float $distanceKm,
        public float $durationMinutes,
        public int $estimatedMinutes,
    ) {}
}
