<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class MasterProductBuilder extends Builder
{
    public function withListRelations(): self
    {
        return $this->with(['category', 'description', 'retailDetail', 'media']);
    }

    public function approvalStatus(?string $status): self
    {
        return $this->when($status, function (self $query, string $status): void {
            match ($status) {
                'approved' => $query->where('is_active', true)->whereDoesntHave('vendorSubmission'),
                default => $query->whereHas(
                    'vendorSubmission',
                    fn (Builder $query): Builder => $query->where('status', $status)->forAdminZones()
                )->with('vendorSubmission.vendor'),
            };
        });
    }

    public function inCategory(null|int|string $categoryId): self
    {
        return $this->when($categoryId, fn (self $query, int|string $categoryId): self => $query->where('category_id', $categoryId));
    }

    public function unitType(?string $unitType): self
    {
        return $this->when($unitType, fn (self $query, string $unitType): self => $query->where('unit_type', $unitType));
    }

    public function active(?bool $isActive): self
    {
        return $this->when($isActive !== null, fn (self $query): self => $query->where('is_active', $isActive));
    }

    public function searchName(?string $search): self
    {
        return $this->when($search, fn (self $query, string $search): self => $query->where('name', 'like', "%{$search}%"));
    }

    public function newest(): self
    {
        return $this->latest('id');
    }
}
