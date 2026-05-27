<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class VendorBuilder extends Builder
{
    public function withListRelations(): self
    {
        return $this->with('media');
    }

    public function type(?string $type): self
    {
        return $this->when($type, fn (self $query, string $type): self => $query->where('type', $type));
    }

    public function active(null|bool|string $isActive): self
    {
        return $this->when($isActive !== null, fn (self $query): self => $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN)));
    }

    public function search(?string $search): self
    {
        return $this->when($search, function (self $query, string $search): void {
            $query->where(function (self $query) use ($search): void {
                $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        });
    }

    public function newest(): self
    {
        return $this->latest();
    }
}
