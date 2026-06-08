<?php

namespace App\Services\Vendor;

use App\DTOs\Vendor\Profile\UpdateProfileDTO;
use App\DTOs\Vendor\Profile\UpdateStatusDTO;
use App\DTOs\Vendor\Profile\UpdateWorkingHoursDTO;
use App\Enums\FileType;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\Storage;

class VendorProfileService
{
    /**
     * Get the full vendor profile with all relations.
     */
    public function getProfile(int $vendorId): Vendor
    {
        return Vendor::with([
            'media',
            'description',
            'workingHours',
            'deliveryZones',
            'activeSubscription.plan',
        ])->findOrFail($vendorId);
    }

    /**
     * Update the vendor's profile information.
     */
    public function updateProfile(int $vendorId, UpdateProfileDTO $dto): Vendor
    {
        $vendor = Vendor::findOrFail($vendorId);

        $data = array_filter([
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'formatted_address' => $dto->formatted_address,
            'latitude' => $dto->latitude,
            'longitude' => $dto->longitude,
        ], fn ($value) => $value !== null);

        if (! empty($data)) {
            $vendor->update($data);
        }

        if ($dto->description !== null) {
            if ($dto->description === '') {
                $vendor->description()?->delete();
            } else {
                $vendor->description()->updateOrCreate(
                    ['vendor_id' => $vendor->id],
                    ['description' => $dto->description]
                );
            }
        }

        if ($dto->has_image && $dto->image) {
            $path = $dto->image->store('vendors', 'public');

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

        return $this->getProfile($vendorId);
    }

    /**
     * Update the vendor's online status.
     */
    public function updateStatus(int $vendorId, UpdateStatusDTO $dto): Vendor
    {
        $vendor = Vendor::findOrFail($vendorId);
        $vendor->update(['status' => $dto->status->value]);

        return $this->getProfile($vendorId);
    }

    /**
     * Bulk replace the vendor's working hours.
     */
    public function updateWorkingHours(int $vendorId, UpdateWorkingHoursDTO $dto): Vendor
    {
        $vendor = Vendor::findOrFail($vendorId);

        $vendor->workingHours()->delete();
        $vendor->workingHours()->createMany($dto->workingHours);

        return $this->getProfile($vendorId);
    }
}
