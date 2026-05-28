<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class OrderBuilder extends Builder
{
    public function withListRelations(): self
    {
        return $this->with([
            'customer.customer',
            'subOrders.vendor',
            'subOrders.items.storeItem.masterProduct',
            'subOrders.items.options.productOption',
            'subOrders.items.options.productOptionValue',
            'subOrders.items.note',
        ]);
    }

    public function status(?string $status): self
    {
        return $this->when($status, fn (self $query, string $status): self => $query->where('status', $status));
    }

    public function type(?string $orderType): self
    {
        return $this->when($orderType, fn (self $query, string $orderType): self => $query->where('order_type', $orderType));
    }

    public function dateFrom(?string $date): self
    {
        return $this->when($date, fn (self $query, string $date): self => $query->whereDate('created_at', '>=', $date));
    }

    public function dateTo(?string $date): self
    {
        return $this->when($date, fn (self $query, string $date): self => $query->whereDate('created_at', '<=', $date));
    }

    public function forCustomer(null|int|string $customerId): self
    {
        return $this->when($customerId, function (self $query, int|string $customerId): void {
            $query->whereHas('customer', fn (Builder $query): Builder => $query->where('customer_id', $customerId));
        });
    }

    public function forVendor(null|int|string $vendorId): self
    {
        return $this->when($vendorId, function (self $query, int|string $vendorId): void {
            $query->whereHas('subOrders', fn (Builder $query): Builder => $query->where('vendor_id', $vendorId));
        });
    }

    public function newest(): self
    {
        $column = $this->model->usesTimestamps() ? $this->model->getCreatedAtColumn() : $this->model->getKeyName();

        return $this->latest($column);
    }
}
