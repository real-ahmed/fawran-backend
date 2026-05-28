<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class AdminBuilder extends Builder
{
    public function withRoles(): self
    {
        return $this->with('roles');
    }

    public function searchIdentity(?string $search): self
    {
        return $this->when($search, function (self $query, string $search): void {
            $query->where(function (self $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }

    public function newest(): self
    {
        $column = $this->model->usesTimestamps() ? $this->model->getCreatedAtColumn() : $this->model->getKeyName();

        return $this->latest($column);
    }
}
