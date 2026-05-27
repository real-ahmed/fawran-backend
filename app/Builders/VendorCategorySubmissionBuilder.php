<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class VendorCategorySubmissionBuilder extends Builder
{
    public function withApprovalRelations(): self
    {
        return $this->with(['category', 'vendor']);
    }

    public function status(?string $status): self
    {
        return $this->when($status, fn (self $query, string $status): self => $query->where('status', $status));
    }

    public function pending(): self
    {
        return $this->status('pending');
    }

    public function newest(): self
    {
        return $this->latest();
    }
}
