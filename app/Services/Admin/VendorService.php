<?php

namespace App\Services\Admin;

use App\Models\Vendor\Vendor;
use App\Traits\Paginatable;
use Illuminate\Http\Request;

class VendorService
{
    use Paginatable;

    public function listVendors(Request $request)
    {
        $query = Vendor::query()->forAdminZones();

        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->query('is_active'));
        }

        return $query->latest()->paginate($this->getPerPageLimit());
    }

    public function createVendor(array $data): Vendor
    {
        return Vendor::create($data);
    }

    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        $vendor->update($data);

        return $vendor;
    }

    public function deleteVendor(Vendor $vendor): void
    {
        $vendor->delete();
    }
}
