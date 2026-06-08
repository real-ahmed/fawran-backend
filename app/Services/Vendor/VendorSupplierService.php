<?php

namespace App\Services\Vendor;

use App\Models\Inventory\Supplier;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorSupplierService
{
    public function listSuppliers(int $vendorId): LengthAwarePaginator
    {
        return Supplier::where('vendor_id', $vendorId)
            ->paginate(20);
    }

    public function createSupplier(int $vendorId, array $data): Supplier
    {
        return Supplier::create([
            'vendor_id' => $vendorId,
            'name' => $data['name'],
            'phone' => $data['phone'],
        ]);
    }

    public function updateSupplier(Supplier $supplier, int $vendorId, array $data): Supplier
    {
        $this->ensureBelongsToVendor($supplier, $vendorId);

        $supplier->update($data);

        return $supplier;
    }

    public function deleteSupplier(Supplier $supplier, int $vendorId): void
    {
        $this->ensureBelongsToVendor($supplier, $vendorId);

        $supplier->delete();
    }

    private function ensureBelongsToVendor(Supplier $supplier, int $vendorId): void
    {
        abort_unless((int) $supplier->vendor_id === $vendorId, 403, 'Unauthorized action.');
    }
}
