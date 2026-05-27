<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class SystemSettingBuilder extends Builder
{
    public function key(?string $key): self
    {
        return $this->when($key, fn (self $query, string $key): self => $query->where('key', $key));
    }

    /**
     * @param  array<int, string>  $keys
     */
    public function keys(array $keys): self
    {
        return $this->whereIn('key', $keys);
    }

    public function group(?string $group): self
    {
        return $this->when($group, fn (self $query, string $group): self => $query->where('group', $group));
    }

    public function ordered(): self
    {
        return $this->orderBy('group')->orderBy('key');
    }
}
