<?php

namespace App\Contracts;

use App\Models\Product\VendorItem;

interface VendorTypeHandler
{
    /**
     * Extra relations to eager-load on vendor items for this type.
     *
     * @return array<int, string>
     */
    public function itemRelations(): array;

    /**
     * Extra validation rules for creating/updating items.
     *
     * @return array<string, mixed>
     */
    public function itemValidationRules(bool $isUpdate = false): array;

    /**
     * Extra data to merge into item API resource.
     *
     * @return array<string, mixed>
     */
    public function transformItemExtras(VendorItem $item): array;

    /**
     * Post-create hook for type-specific item data (e.g., inventory, dish details).
     *
     * @param  array<string, mixed>  $data
     */
    public function afterItemCreated(VendorItem $item, array $data): void;

    /**
     * Post-update hook for type-specific item data.
     *
     * @param  array<string, mixed>  $data
     */
    public function afterItemUpdated(VendorItem $item, array $data): void;

    /**
     * Whether this vendor type supports inventory tracking.
     */
    public function hasInventory(): bool;
}
