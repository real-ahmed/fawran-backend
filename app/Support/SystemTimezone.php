<?php

namespace App\Support;

use App\Models\Platform\SystemSetting;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Throwable;

class SystemTimezone
{
    public const SETTING_KEY = 'timezone';

    public static function name(): string
    {
        try {
            $timezone = SystemSetting::cachedValue(self::SETTING_KEY, config('app.timezone', 'UTC')) ?: 'UTC';
        } catch (Throwable) {
            $timezone = config('app.timezone', 'UTC');
        }

        if (in_array($timezone, timezone_identifiers_list(), true)) {
            return $timezone;
        }

        return config('app.timezone', 'UTC');
    }

    public static function serialize(DateTimeInterface $date): string
    {
        return CarbonImmutable::instance($date)
            ->setTimezone(self::name())
            ->format(DateTimeInterface::ATOM);
    }

    public static function dateTimeInputToUtc(string $value): string
    {
        return CarbonImmutable::parse($value, self::name())
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');
    }
}
