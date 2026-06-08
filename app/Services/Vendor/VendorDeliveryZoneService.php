<?php

namespace App\Services\Vendor;

use App\Models\Geo\VendorDeliveryZone;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorDeliveryZoneService
{
    public function listZones(int $vendorId): LengthAwarePaginator
    {
        return VendorDeliveryZone::where('vendor_id', $vendorId)
            ->with('deliveryZone')
            ->paginate(20);
    }

    public function createZone(int $vendorId, array $data): VendorDeliveryZone
    {
        $zone = VendorDeliveryZone::firstOrCreate(
            [
                'vendor_id' => $vendorId,
                'delivery_zone_id' => $data['delivery_zone_id'],
            ],
            [
                'min_order_amount' => $data['min_order_amount'],
                'estimated_delivery_time' => $data['estimated_delivery_time'],
            ]
        );

        return $zone->load('deliveryZone');
    }

    public function updateZone(VendorDeliveryZone $zone, int $vendorId, array $data): VendorDeliveryZone
    {
        $this->ensureBelongsToVendor($zone, $vendorId);

        $zone->update($data);

        return $zone->load('deliveryZone');
    }

    public function deleteZone(VendorDeliveryZone $zone, int $vendorId): void
    {
        $this->ensureBelongsToVendor($zone, $vendorId);

        $zone->delete();
    }

    private function ensureBelongsToVendor(VendorDeliveryZone $zone, int $vendorId): void
    {
        abort_unless((int) $zone->vendor_id === $vendorId, 403, 'Unauthorized action.');
    }
}
