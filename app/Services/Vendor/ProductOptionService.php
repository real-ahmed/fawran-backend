<?php

namespace App\Services\Vendor;

use App\DTOs\Vendor\Product\ProductOptionDataDTO;
use App\Models\Product\ProductOption;
use App\Models\Product\VendorItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductOptionService
{
    public function getOptions(VendorItem $item, int $vendorId): Collection
    {
        $this->ensureItemBelongsToVendor($item, $vendorId);

        return $item->productOptions()->with('values')->get();
    }

    public function createOption(VendorItem $item, int $vendorId, ProductOptionDataDTO $dto): ProductOption
    {
        $this->ensureItemBelongsToVendor($item, $vendorId);

        return DB::transaction(function () use ($item, $dto) {
            $option = $item->productOptions()->create([
                'name' => $dto->name,
                'is_required' => $dto->is_required,
                'max_selections' => $dto->max_selections,
            ]);

            foreach ($dto->values as $valueDto) {
                $option->values()->create([
                    'name' => $valueDto->name,
                    'additional_price' => $valueDto->additional_price,
                    'is_available' => $valueDto->is_available,
                ]);
            }

            return $option->load('values');
        });
    }

    public function getOption(VendorItem $item, ProductOption $option, int $vendorId): ProductOption
    {
        $this->ensureOptionBelongsToItem($item, $option, $vendorId);

        return $option->load('values');
    }

    public function updateOption(VendorItem $item, ProductOption $option, int $vendorId, ProductOptionDataDTO $dto): ProductOption
    {
        $this->ensureOptionBelongsToItem($item, $option, $vendorId);

        return DB::transaction(function () use ($option, $dto) {
            $data = [];
            if (! empty($dto->name)) {
                $data['name'] = $dto->name;
            }
            if ($dto->is_required !== null) {
                $data['is_required'] = $dto->is_required;
            }
            if ($dto->max_selections !== null) {
                $data['max_selections'] = $dto->max_selections;
            }

            if (! empty($data)) {
                $option->update($data);
            }

            if (! empty($dto->values)) {
                foreach ($dto->values as $valueDto) {
                    if ($valueDto->is_deleted && $valueDto->id) {
                        $option->values()->where('id', $valueDto->id)->delete();

                        continue;
                    }

                    if ($valueDto->id) {
                        $valueModel = $option->values()->find($valueDto->id);
                        if ($valueModel) {
                            $valueData = [];
                            if (! empty($valueDto->name)) {
                                $valueData['name'] = $valueDto->name;
                            }
                            if ($valueDto->additional_price !== null) {
                                $valueData['additional_price'] = $valueDto->additional_price;
                            }
                            if ($valueDto->is_available !== null) {
                                $valueData['is_available'] = $valueDto->is_available;
                            }
                            $valueModel->update($valueData);
                        }
                    } else {
                        $option->values()->create([
                            'name' => $valueDto->name,
                            'additional_price' => $valueDto->additional_price,
                            'is_available' => $valueDto->is_available,
                        ]);
                    }
                }
            }

            return $option->fresh('values');
        });
    }

    public function deleteOption(VendorItem $item, ProductOption $option, int $vendorId): void
    {
        $this->ensureOptionBelongsToItem($item, $option, $vendorId);

        $option->delete();
    }

    public function ensureOptionBelongsToItem(VendorItem $item, ProductOption $option, int $vendorId): void
    {
        $this->ensureItemBelongsToVendor($item, $vendorId);

        abort_unless((int) $option->vendor_item_id === (int) $item->id, 403, 'Unauthorized action.');
    }

    private function ensureItemBelongsToVendor(VendorItem $item, int $vendorId): void
    {
        abort_unless((int) $item->vendor_id === $vendorId, 403, 'Unauthorized action.');
    }
}
