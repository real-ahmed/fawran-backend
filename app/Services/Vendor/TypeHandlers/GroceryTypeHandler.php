<?php

namespace App\Services\Vendor\TypeHandlers;

use App\Contracts\VendorTypeHandler;
use App\Models\Product\VendorItem;

class GroceryTypeHandler implements VendorTypeHandler
{
    /**
     * @return array<int, string>
     */
    public function itemRelations(): array
    {
        return ['inventory'];
    }

    /**
     * @return array<string, mixed>
     */
    public function itemValidationRules(bool $isUpdate = false): array
    {
        $prefix = $isUpdate ? 'sometimes' : 'nullable';

        return [
            'current_stock' => [$prefix, 'numeric', 'min:0'],
            'low_stock_threshold' => [$prefix, 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function transformItemExtras(VendorItem $item): array
    {
        return [
            'current_stock' => $item->relationLoaded('inventory')
                ? (float) ($item->inventory?->current_stock ?? 0)
                : null,
            'low_stock_threshold' => $item->relationLoaded('inventory')
                ? (float) ($item->inventory?->low_stock_threshold ?? 0)
                : null,
            'is_low_stock' => $item->relationLoaded('inventory') && $item->inventory
                ? $item->inventory->current_stock <= $item->inventory->low_stock_threshold
                : false,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function afterItemCreated(VendorItem $item, array $data): void
    {
        if (isset($data['current_stock']) || isset($data['low_stock_threshold'])) {
            $item->inventory()->create([
                'current_stock' => $data['current_stock'] ?? 0,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? 0,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function afterItemUpdated(VendorItem $item, array $data): void
    {
        if (isset($data['current_stock']) || isset($data['low_stock_threshold'])) {
            $item->inventory()->updateOrCreate(
                ['vendor_item_id' => $item->id],
                array_filter([
                    'current_stock' => $data['current_stock'] ?? null,
                    'low_stock_threshold' => $data['low_stock_threshold'] ?? null,
                ], fn ($value) => $value !== null)
            );
        }
    }

    public function hasInventory(): bool
    {
        return true;
    }
}
