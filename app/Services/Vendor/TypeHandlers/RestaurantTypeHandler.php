<?php

namespace App\Services\Vendor\TypeHandlers;

use App\Contracts\VendorTypeHandler;
use App\Models\Product\VendorItem;

class RestaurantTypeHandler implements VendorTypeHandler
{
    /**
     * @return array<int, string>
     */
    public function itemRelations(): array
    {
        return ['restaurantDishDetail', 'productOptions.values'];
    }

    /**
     * @return array<string, mixed>
     */
    public function itemValidationRules(bool $isUpdate = false): array
    {
        $prefix = $isUpdate ? 'sometimes' : 'nullable';

        return [
            'preparation_time' => [$prefix, 'integer', 'min:1', 'max:480'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function transformItemExtras(VendorItem $item): array
    {
        return [
            'preparation_time' => $item->relationLoaded('restaurantDishDetail')
                ? $item->restaurantDishDetail?->preparation_time
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function afterItemCreated(VendorItem $item, array $data): void
    {
        if (isset($data['preparation_time'])) {
            $item->restaurantDishDetail()->create([
                'preparation_time' => $data['preparation_time'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function afterItemUpdated(VendorItem $item, array $data): void
    {
        if (isset($data['preparation_time'])) {
            $item->restaurantDishDetail()->updateOrCreate(
                ['vendor_item_id' => $item->id],
                ['preparation_time' => $data['preparation_time']]
            );
        }
    }

    public function hasInventory(): bool
    {
        return false;
    }
}
