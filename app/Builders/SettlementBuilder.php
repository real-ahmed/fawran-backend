<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class SettlementBuilder extends Builder
{
    public function withListRelations(): self
    {
        return $this->with(['execution', 'note']);
    }

    public function type(?string $settlementType): self
    {
        return $this->when($settlementType, fn (self $query, string $settlementType): self => $query->where('settlement_type', $settlementType));
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
