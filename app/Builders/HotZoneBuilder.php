<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class HotZoneBuilder extends Builder
{
    public function withListRelations(): self
    {
        return $this->with(['manualHotZone', 'autoHotZone']);
    }

    public function active(?bool $isActive): self
    {
        return $this->when($isActive !== null, fn (self $query): self => $query->where('is_active', $isActive));
    }

    public function intensity(?string $intensity): self
    {
        return $this->when($intensity, fn (self $query, string $intensity): self => $query->where('intensity', $intensity));
    }

    public function newest(): self
    {
        return $this->latest('starts_at');
    }
}
