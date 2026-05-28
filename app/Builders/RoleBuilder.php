<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class RoleBuilder extends Builder
{
    public function guard(string $guard): self
    {
        return $this->where('guard_name', $guard);
    }

    public function vendor(null|int|string $vendorId): self
    {
        return $this->when($vendorId !== null, fn (self $query): self => $query->where('vendor_id', $vendorId));
    }

    public function searchName(?string $search): self
    {
        return $this->when($search, fn (self $query, string $search): self => $query->where('name', 'like', "%{$search}%"));
    }

    public function withPermissions(): self
    {
        return $this->with('permissions');
    }

    public function newest(): self
    {
        $column = $this->model->usesTimestamps() ? $this->model->getCreatedAtColumn() : $this->model->getKeyName();

        return $this->latest($column);
    }
}
