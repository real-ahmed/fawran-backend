<?php

namespace App\Services\Admin;

use App\DTOs\Admin\Vendor\VendorDataDTO;
use App\DTOs\Admin\Vendor\VendorFilterDTO;
use App\Enums\FileType;
use App\Models\Vendor\Vendor;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\Storage;

class VendorService
{
    use Paginatable;

    public function listVendors(VendorFilterDTO $filters)
    {
        return Vendor::query()
            ->withListRelations()
            ->forAdminZones()
            ->type($filters->type)
            ->active($filters->is_active)
            ->search($filters->search)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function createVendor(VendorDataDTO $dto): Vendor
    {
        $data = [];
        if ($dto->owner_id !== null) {
            $data['owner_id'] = $dto->owner_id;
        }
        if ($dto->name !== null) {
            $data['name'] = $dto->name;
        }
        if ($dto->email !== null) {
            $data['email'] = $dto->email;
        }
        if ($dto->phone !== null) {
            $data['phone'] = $dto->phone;
        }
        if ($dto->formatted_address !== null) {
            $data['formatted_address'] = $dto->formatted_address;
        }
        if ($dto->type !== null) {
            $data['type'] = $dto->type;
        }
        if ($dto->latitude !== null) {
            $data['latitude'] = $dto->latitude;
        }
        if ($dto->longitude !== null) {
            $data['longitude'] = $dto->longitude;
        }
        if ($dto->is_active !== null) {
            $data['is_active'] = $dto->is_active;
        }
        if ($dto->status !== null) {
            $data['status'] = $dto->status;
        }

        $vendor = Vendor::create($data);

        if ($dto->has_image && $dto->image) {
            $path = $dto->image->store('vendors', 'public');
            $vendor->media()->create([
                'file_path' => $path,
                'file_type' => FileType::Image,
                'is_primary' => true,
            ]);
        }

        if ($dto->has_working_hours && ! empty($dto->working_hours)) {
            $vendor->workingHours()->createMany($dto->working_hours);
        }

        if ($dto->has_delivery_zones && ! empty($dto->delivery_zones)) {
            $vendor->deliveryZones()->createMany($dto->delivery_zones);
        }

        return $vendor->load(['media', 'workingHours', 'deliveryZones']);
    }

    public function getVendor(Vendor $vendor): Vendor
    {
        return $vendor->load(['media', 'workingHours', 'deliveryZones', 'owner']);
    }

    public function updateVendor(Vendor $vendor, VendorDataDTO $dto): Vendor
    {
        $data = [];
        if ($dto->owner_id !== null) {
            $data['owner_id'] = $dto->owner_id;
        }
        if ($dto->name !== null) {
            $data['name'] = $dto->name;
        }
        if ($dto->email !== null) {
            $data['email'] = $dto->email;
        }
        if ($dto->phone !== null) {
            $data['phone'] = $dto->phone;
        }
        if ($dto->formatted_address !== null) {
            $data['formatted_address'] = $dto->formatted_address;
        }
        if ($dto->type !== null) {
            $data['type'] = $dto->type;
        }
        if ($dto->latitude !== null) {
            $data['latitude'] = $dto->latitude;
        }
        if ($dto->longitude !== null) {
            $data['longitude'] = $dto->longitude;
        }
        if ($dto->is_active !== null) {
            $data['is_active'] = $dto->is_active;
        }
        if ($dto->status !== null) {
            $data['status'] = $dto->status;
        }

        if (! empty($data)) {
            $vendor->update($data);
        }

        if ($dto->has_image && $dto->image) {
            $path = $dto->image->store('vendors', 'public');

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

        if ($dto->has_working_hours) {
            $vendor->workingHours()->delete();
            if (! empty($dto->working_hours)) {
                $vendor->workingHours()->createMany($dto->working_hours);
            }
        }

        if ($dto->has_delivery_zones) {
            $vendor->deliveryZones()->delete();
            if (! empty($dto->delivery_zones)) {
                $vendor->deliveryZones()->createMany($dto->delivery_zones);
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
