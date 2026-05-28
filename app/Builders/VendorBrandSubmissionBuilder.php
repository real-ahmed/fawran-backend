<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class VendorBrandSubmissionBuilder extends Builder
{
    public function withApprovalRelations(): self
    {
        return $this->with(['brand', 'vendor']);
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
        $column = $this->model->usesTimestamps() ? $this->model->getCreatedAtColumn() : $this->model->getKeyName();

        return $this->latest($column);
    }
}
