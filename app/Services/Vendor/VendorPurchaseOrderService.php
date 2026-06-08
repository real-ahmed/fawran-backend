<?php

namespace App\Services\Vendor;

use App\Enums\PurchaseOrderStatus;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use App\Models\Product\VendorItem;
use App\Models\Product\VendorItemInventory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VendorPurchaseOrderService
{
    public function listPurchaseOrders(int $vendorId): LengthAwarePaginator
    {
        return PurchaseOrder::with($this->relations())
            ->where('vendor_id', $vendorId)
            ->latest('id')
            ->paginate(20);
    }

    public function createPurchaseOrder(int $vendorId, array $data): PurchaseOrder
    {
        $this->ensureSupplierBelongsToVendor((int) $data['supplier_id'], $vendorId);
        $this->ensureItemsBelongToVendor($data['items'], $vendorId);

        return DB::transaction(function () use ($vendorId, $data) {
            $totalCost = collect($data['items'])->sum(function ($item) {
                return $item['quantity'] * $item['cost_price'];
            });

            $purchaseOrder = PurchaseOrder::create([
                'vendor_id' => $vendorId,
                'supplier_id' => $data['supplier_id'],
                'total_cost' => $totalCost,
                'status' => PurchaseOrderStatus::Pending,
            ]);

            foreach ($data['items'] as $itemData) {
                $purchaseOrder->items()->create([
                    'vendor_item_id' => $itemData['vendor_item_id'],
                    'quantity' => $itemData['quantity'],
                    'cost_price' => $itemData['cost_price'],
                ]);
            }

            return $purchaseOrder->load($this->relations());
        });
    }

    public function updatePurchaseOrderStatus(PurchaseOrder $purchaseOrder, int $vendorId, string $status): PurchaseOrder
    {
        $this->ensureBelongsToVendor($purchaseOrder, $vendorId);
        $this->ensureCanUpdateStatus($purchaseOrder);

        return DB::transaction(function () use ($purchaseOrder, $status) {
            $purchaseOrder->update(['status' => $status]);

            if ($status === PurchaseOrderStatus::Received->value) {
                foreach ($purchaseOrder->items as $item) {
                    $inventory = VendorItemInventory::where('vendor_item_id', $item->vendor_item_id)->first();

                    if ($inventory) {
                        $inventory->increment('current_stock', $item->quantity);
                    } else {
                        VendorItemInventory::create([
                            'vendor_item_id' => $item->vendor_item_id,
                            'current_stock' => $item->quantity,
                            'low_stock_threshold' => 10,
                        ]);
                    }
                }
            }

            return $purchaseOrder->fresh($this->relations());
        });
    }

    public function getPurchaseOrder(PurchaseOrder $purchaseOrder, int $vendorId): PurchaseOrder
    {
        $this->ensureBelongsToVendor($purchaseOrder, $vendorId);

        return $purchaseOrder->load($this->relations());
    }

    private function ensureBelongsToVendor(PurchaseOrder $purchaseOrder, int $vendorId): void
    {
        abort_unless((int) $purchaseOrder->vendor_id === $vendorId, 403, 'Unauthorized action.');
    }

    private function ensureCanUpdateStatus(PurchaseOrder $purchaseOrder): void
    {
        $status = $purchaseOrder->status instanceof PurchaseOrderStatus
            ? $purchaseOrder->status
            : PurchaseOrderStatus::from((string) $purchaseOrder->status);

        if ($status === PurchaseOrderStatus::Received) {
            throw new InvalidArgumentException('Cannot update a received purchase order.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return ['supplier', 'items.storeItem.masterProduct'];
    }

    private function ensureSupplierBelongsToVendor(int $supplierId, int $vendorId): void
    {
        Supplier::query()
            ->where('vendor_id', $vendorId)
            ->findOrFail($supplierId);
    }

    /**
     * @param  array<int, array{vendor_item_id: int|string}>  $items
     */
    private function ensureItemsBelongToVendor(array $items, int $vendorId): void
    {
        $itemIds = collect($items)
            ->pluck('vendor_item_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $ownedItemCount = VendorItem::query()
            ->where('vendor_id', $vendorId)
            ->whereIn('id', $itemIds)
            ->count();

        abort_unless($ownedItemCount === $itemIds->count(), 422, 'Some purchase order items do not belong to this vendor.');
    }
}
