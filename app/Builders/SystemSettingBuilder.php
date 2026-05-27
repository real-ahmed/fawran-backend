<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class SystemSettingBuilder extends Builder
{
    public function group(?string $group): self
    {
        return $this->when($group, fn (self $query, string $group): self => $query->where('group', $group));
    }

    public function ordered(): self
    {
        return $this->orderBy('group')->orderBy('key');
    }
}
