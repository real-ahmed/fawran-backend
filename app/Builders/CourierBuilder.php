<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class CourierBuilder extends Builder
{
    public function withListRelations(): self
    {
        return $this->with(['user', 'document', 'approval']);
    }

    public function search(?string $search): self
    {
        return $this->when($search, function (self $query, string $search): void {
            $query->where(function (self $query) use ($search): void {
                $query->where('plate_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        });
    }

    public function online(?bool $isOnline): self
    {
        return $this->when($isOnline !== null, fn (self $query): self => $query->where('is_online', $isOnline));
    }

    public function blocked(?bool $isBlocked): self
    {
        return $this->when($isBlocked !== null, fn (self $query): self => $query->where('is_blocked', $isBlocked));
    }

    public function vehicleType(?string $vehicleType): self
    {
        return $this->when($vehicleType, fn (self $query, string $vehicleType): self => $query->where('vehicle_type', $vehicleType));
    }

    public function inDeliveryZone(null|int|string $deliveryZoneId): self
    {
        return $this->when($deliveryZoneId, function (self $query, int|string $deliveryZoneId): void {
            $query->whereHas('location', function (Builder $query) use ($deliveryZoneId): void {
                $query->whereExists(function (QueryBuilder $query) use ($deliveryZoneId): void {
                    $query->selectRaw('1')
                        ->from('delivery_zones')
                        ->where('id', $deliveryZoneId)
                        ->whereRaw("ST_Contains(delivery_zones.polygon, ST_GeomFromText(CONCAT('POINT(', courier_locations.longitude, ' ', courier_locations.latitude, ')')))");
                });
            });
        });
    }

    public function approvalStatus(?string $approvalStatus): self
    {
        return match ($approvalStatus) {
            'pending' => $this->whereDoesntHave('approval')->whereNull('rejected_at'),
            'approved' => $this->whereHas('approval'),
            'rejected' => $this->whereDoesntHave('approval')->whereNotNull('rejected_at'),
            default => $this,
        };
    }

    public function newest(): self
    {
        return $this->latest('id');
    }
}
