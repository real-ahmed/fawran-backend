<?php

namespace App\Services\Vendor;

use App\DTOs\Vendor\Product\UpdateVendorItemStatusDTO;
use App\DTOs\Vendor\Product\VendorItemDataDTO;
use App\Models\Product\VendorItem;
use App\Models\Vendor\Vendor;
use App\Traits\Paginatable;
use Illuminate\Pagination\CursorPaginator;

class VendorItemService
{
    use Paginatable;

    public function __construct(
        private VendorTypeRegistry $registry
    ) {}

    public function getItems(Vendor $vendor): CursorPaginator
    {
        $handler = $this->registry->handler($vendor->type);

        return $vendor->storeItems()
            ->with($this->baseRelations())
            ->with($handler->itemRelations())
            ->latest('id')
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function createItem(Vendor $vendor, VendorItemDataDTO $dto): VendorItem
    {
        $data = [
            'master_product_id' => $dto->master_product_id,
            'price' => $dto->price,
        ];

        if ($dto->is_available !== null) {
            $data['is_available'] = $dto->is_available;
        }

        $item = $vendor->storeItems()->create($data);

        $handler = $this->registry->handler($vendor->type);
        $handler->afterItemCreated($item, $dto->extras);

        return $item->load(array_merge($this->baseRelations(), $handler->itemRelations()));
    }

    public function getItem(VendorItem $item, Vendor $vendor): VendorItem
    {
        $this->ensureBelongsToVendor($item, $vendor);

        $handler = $this->registry->handler($vendor->type);

        return $item->load(array_merge($this->baseRelations(), $handler->itemRelations()));
    }

    public function updateItem(VendorItem $item, Vendor $vendor, VendorItemDataDTO $dto): VendorItem
    {
        $this->ensureBelongsToVendor($item, $vendor);

        $data = [];
        if ($dto->price !== null) {
            $data['price'] = $dto->price;
        }
        if ($dto->is_available !== null) {
            $data['is_available'] = $dto->is_available;
        }

        if (! empty($data)) {
            $item->update($data);
        }

        $handler = $this->registry->handler($vendor->type);
        $handler->afterItemUpdated($item, $dto->extras);

        return $item->load(array_merge($this->baseRelations(), $handler->itemRelations()));
    }

    public function updateStatus(VendorItem $item, Vendor $vendor, UpdateVendorItemStatusDTO $dto): VendorItem
    {
        $this->ensureBelongsToVendor($item, $vendor);

        $item->update(['is_available' => $dto->is_available]);

        $handler = $this->registry->handler($vendor->type);

        return $item->load(array_merge($this->baseRelations(), $handler->itemRelations()));
    }

    public function deleteItem(VendorItem $item, Vendor $vendor): void
    {
        $this->ensureBelongsToVendor($item, $vendor);

        $item->delete();
    }

    private function ensureBelongsToVendor(VendorItem $item, Vendor $vendor): void
    {
        abort_unless((int) $item->vendor_id === (int) $vendor->id, 403, 'Unauthorized action.');
    }

    /**
     * @return array<int, string>
     */
    private function baseRelations(): array
    {
        return ['store', 'masterProduct.media', 'masterProduct.category'];
    }
}
