<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class CategoryBuilder extends Builder
{
    public function withListRelations(): self
    {
        return $this->with(['hierarchy', 'icon']);
    }

    public function searchName(?string $search): self
    {
        return $this->when($search, function (self $query, string $search): void {
            $query->where(function (self $query) use ($search): void {
                $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%");
            });
        });
    }

    public function approvalStatus(?string $status): self
    {
        return $this->when($status, function (self $query, string $status): void {
            $query->whereHas('vendorSubmission', fn (Builder $query): Builder => $query->where('status', $status)->forAdminZones());
        });
    }

    public function withVendorSubmission(): self
    {
        return $this->with('vendorSubmission.vendor');
    }
}
