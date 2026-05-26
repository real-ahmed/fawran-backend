<?php

namespace App\Traits;

trait Paginatable
{
    /**
     * Get the standardized per_page limit for pagination globally across the system.
     * Caps the limit at 100 to prevent database overload.
     */
    protected function getPerPageLimit(?int $requestedPerPage = null): int
    {
        $perPage = $requestedPerPage ?? request()->input('per_page', 15);

        return min((int) $perPage, 100);
    }
}
