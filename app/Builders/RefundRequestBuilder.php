<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class RefundRequestBuilder extends Builder
{
    public function withListRelations(): self
    {
        return $this->with(['customer', 'order']);
    }

    public function status(?string $status): self
    {
        return $this->when($status, fn (self $query, string $status): self => $query->where('status', $status));
    }

    public function newest(): self
    {
        return $this->latest('created_at');
    }
}
