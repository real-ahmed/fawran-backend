<?php

namespace App\Services\Vendor;

use App\Models\Product\VendorItemInventory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VendorInventoryService
{
    public function listInventory(int $vendorId): LengthAwarePaginator
    {
        return VendorItemInventory::whereHas('storeItem', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })
            ->with(['storeItem.masterProduct'])
            ->paginate(20);
    }

    public function updateInventory(VendorItemInventory $inventory, int $vendorId, array $data): VendorItemInventory
    {
        $this->ensureInventoryBelongsToVendor($inventory, $vendorId);

        $inventory->update($data);

        return $inventory->load(['storeItem.masterProduct']);
    }

    public function adjustInventory(int $vendorId, int $vendorItemId, float $adjustment, ?string $reason = null): VendorItemInventory
    {
        return DB::transaction(function () use ($vendorId, $vendorItemId, $adjustment) {
            $inventory = VendorItemInventory::query()
                ->whereKey($vendorItemId)
                ->whereHas('storeItem', function ($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                })
                ->with(['storeItem.masterProduct'])
                ->lockForUpdate()
                ->firstOrFail();

            $newStock = (float) $inventory->current_stock + $adjustment;

            if ($newStock < 0) {
                throw new HttpException(422, __('messages.insufficient_stock', [
                    'item' => $this->productName($inventory),
                ]));
            }

            $inventory->current_stock = $newStock;
            $inventory->save();

            return $inventory;
        });
    }

    private function ensureInventoryBelongsToVendor(VendorItemInventory $inventory, int $vendorId): void
    {
        $inventory->loadMissing('storeItem');

        abort_unless((int) $inventory->storeItem?->vendor_id === $vendorId, 403, 'Unauthorized action.');
    }

    private function productName(VendorItemInventory $inventory): string
    {
        $name = $inventory->storeItem?->masterProduct?->name;

        if (is_array($name)) {
            return (string) ($name[app()->getLocale()] ?? $name['en'] ?? "#{$inventory->vendor_item_id}");
        }

        return (string) ($name ?? "#{$inventory->vendor_item_id}");
    }
}
