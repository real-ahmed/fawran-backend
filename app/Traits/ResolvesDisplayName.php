<?php

namespace App\Traits;

trait ResolvesDisplayName
{
    protected function displayName(array|string|null $name): string
    {
        if (is_string($name)) {
            return $name;
        }

        $displayName = $name[app()->getLocale()]
            ?? $name['en']
            ?? $name['ar']
            ?? current($name ?: []);

        return $displayName ?: __('messages.unknown');
    }
}
