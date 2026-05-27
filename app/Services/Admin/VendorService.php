<?php

namespace App\Services\Admin;

use App\Enums\FileType;
use App\Models\Vendor\Vendor;
use App\Traits\Paginatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorService
{
    use Paginatable;

    public function listVendors(Request $request)
    {
        return Vendor::query()
            ->withListRelations()
            ->forAdminZones()
            ->type($request->query('type'))
            ->active($request->has('is_active') ? $request->query('is_active') : null)
            ->search($request->query('search'))
            ->newest()
            ->paginate($this->getPerPageLimit());
    }

    public function createVendor(array $data): Vendor
    {
        $image = $data['image'] ?? null;
        $workingHours = $data['working_hours'] ?? [];
        $deliveryZones = $data['delivery_zones'] ?? [];

        unset($data['image'], $data['working_hours'], $data['delivery_zones']);

        $vendor = Vendor::create($data);

        if ($image) {
            $path = $image->store('vendors', 'public');
            $vendor->media()->create([
                'file_path' => $path,
                'file_type' => FileType::Image,
                'is_primary' => true,
            ]);
        }

        if (! empty($workingHours)) {
            $vendor->workingHours()->createMany($workingHours);
        }

        if (! empty($deliveryZones)) {
            $vendor->deliveryZones()->createMany($deliveryZones);
        }

        return $vendor->load(['media', 'workingHours', 'deliveryZones']);
    }

    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        $hasWorkingHours = array_key_exists('working_hours', $data);
        $hasDeliveryZones = array_key_exists('delivery_zones', $data);

        $image = $data['image'] ?? null;
        $workingHours = $data['working_hours'] ?? [];
        $deliveryZones = $data['delivery_zones'] ?? [];

        unset($data['image'], $data['working_hours'], $data['delivery_zones']);

        $vendor->update($data);

        if ($image) {
            $path = $image->store('vendors', 'public');

            // Delete old primary media if exists
            $oldMedia = $vendor->media()->where('is_primary', true)->first();
            if ($oldMedia) {
                Storage::disk('public')->delete($oldMedia->file_path);
                $oldMedia->delete();
            }

            $vendor->media()->create([
                'file_path' => $path,
                'file_type' => FileType::Image,
                'is_primary' => true,
            ]);
        }

        if ($hasWorkingHours) {
            $vendor->workingHours()->delete();
            if (! empty($workingHours)) {
                $vendor->workingHours()->createMany($workingHours);
            }
        }

        if ($hasDeliveryZones) {
            $vendor->deliveryZones()->delete();
            if (! empty($deliveryZones)) {
                $vendor->deliveryZones()->createMany($deliveryZones);
            }
        }

        return $vendor->load(['media', 'workingHours', 'deliveryZones']);
    }

    public function deleteVendor(Vendor $vendor): void
    {
        $media = $vendor->media()->get();
        foreach ($media as $item) {
            Storage::disk('public')->delete($item->file_path);
            $item->delete();
        }
        $vendor->delete();
    }
}
