<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    public function searchIdentity(?string $search): self
    {
        return $this->when($search, function (self $query, string $search): void {
            $query->where(function (self $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        });
    }

    public function active(?bool $isActive): self
    {
        return $this->when($isActive !== null, fn (self $query): self => $query->where('is_active', $isActive));
    }

    public function newest(): self
    {
        return $this->latest();
    }
}
