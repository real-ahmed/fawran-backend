<?php

namespace App\Services\Admin\Catalog;

use App\DTOs\Admin\Catalog\BrandDataDTO;
use App\DTOs\Admin\Catalog\BrandFilterDTO;
use App\Enums\FileType;
use App\Models\Catalog\Brand;
use App\Notifications\Catalog\BrandApprovedNotification;
use App\Traits\Paginatable;
use App\Traits\ResolvesDisplayName;
use Illuminate\Support\Facades\Storage;

class BrandService
{
    use Paginatable, ResolvesDisplayName;

    public function listBrands(BrandFilterDTO $filters)
    {
        return Brand::query()
            ->withListRelations()
            ->where(function ($query): void {
                $query->whereDoesntHave('vendorSubmission')
                    ->orWhereHas('vendorSubmission', fn ($query) => $query->forAdminZones());
            })
            ->searchName($filters->search)
            ->approvalStatus($filters->approval_status)
            ->active($filters->is_active)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit())
            ->withQueryString();
    }

    public function createBrand(BrandDataDTO $dto): Brand
    {
        $data = $dto->toArray();
        $image = $dto->image;
        unset($data['image']);

        $brand = Brand::create($data);

        if ($image) {
            $path = $image->store('brands', 'public');
            $brand->media()->create([
                'file_path' => $path,
                'file_type' => FileType::Image,
                'is_primary' => true,
            ]);
        }

        return $brand->load('media');
    }

    public function getBrand(Brand $brand): Brand
    {
        $this->ensureSubmissionVisible($brand);

        return $brand->load('media');
    }

    public function updateBrand(Brand $brand, BrandDataDTO $dto): Brand
    {
        $this->ensureSubmissionVisible($brand);

        $data = $dto->toArray();
        $image = $dto->image;
        unset($data['image']);

        $brand->update($data);

        if ($image) {
            $path = $image->store('brands', 'public');

            $oldMedia = $brand->media()->where('is_primary', true)->first();
            if ($oldMedia) {
                Storage::disk('public')->delete($oldMedia->file_path);
                $oldMedia->delete();
            }

            $brand->media()->create([
                'file_path' => $path,
                'file_type' => FileType::Image,
                'is_primary' => true,
            ]);
        }

        return $brand->load('media');
    }

    public function deleteBrand(Brand $brand): void
    {
        $this->ensureSubmissionVisible($brand);

        $media = $brand->media()->get();
        foreach ($media as $item) {
            Storage::disk('public')->delete($item->file_path);
            $item->delete();
        }
        $brand->delete();
    }

    public function approveBrand(Brand $brand): void
    {
        $this->ensureSubmissionVisible($brand);

        $brand->update(['is_active' => true]);
        $brand->loadMissing('vendorSubmission.vendor.owner');

        $submission = $brand->vendorSubmission;

        if (! $submission) {
            return;
        }

        if ($submission->vendor?->owner) {
            $submission->vendor->owner->notify(new BrandApprovedNotification($this->displayName($brand->name)));
        }

        $submission->delete();
    }

    public function rejectBrand(Brand $brand): void
    {
        $this->ensureSubmissionVisible($brand);

        $brand->vendorSubmission()->update(['status' => 'rejected']);
    }

    private function ensureSubmissionVisible(Brand $brand): void
    {
        $brand->loadMissing('vendorSubmission');
        $brand->vendorSubmission?->ensureVisibleToAdminZones();
    }
}
