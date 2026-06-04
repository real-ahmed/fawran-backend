<?php

namespace App\Traits;

use App\Support\SystemTimezone;
use DateTimeInterface;

trait HasSystemTimezoneDates
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return SystemTimezone::serialize($date);
    }
}
