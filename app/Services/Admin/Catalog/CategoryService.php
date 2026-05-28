<?php

namespace App\Services\Admin\Catalog;

use App\Enums\FileType;
use App\Models\Catalog\Category;
use App\Notifications\Catalog\CategoryApprovedNotification;
use App\Traits\Paginatable;
use App\Traits\ResolvesDisplayName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    use Paginatable, ResolvesDisplayName;

    public function listCategories(?string $search = null, ?string $approvalStatus = null, ?bool $isActive = null)
    {
        return Category::query()
            ->withListRelations()
            ->searchName($search)
            ->approvalStatus($approvalStatus)
            ->active($isActive)
            ->withVendorSubmission()
            ->newest()
            ->cursorPaginate($this->getPerPageLimit())
            ->withQueryString();
    }

    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $category = Category::create([
                'name' => $data['name'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (isset($data['parent_category_id'])) {
                $category->hierarchy()->create([
                    'parent_category_id' => $data['parent_category_id'],
                ]);
            }

            if (! empty($data['icon_class'])) {
                $category->icon()->create([
                    'icon_class' => $data['icon_class'],
                ]);
            }

            $image = $data['image'] ?? null;
            if ($image) {
                $path = $image->store('categories', 'public');
                $category->media()->create([
                    'file_path' => $path,
                    'file_type' => FileType::Image,
                    'is_primary' => true,
                ]);
            }

            return $category->load(['hierarchy', 'icon', 'media']);
        });
    }

    public function updateCategory(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($data, $category) {
            $updateData = collect($data)->only(['name', 'is_active'])->toArray();
            if (! empty($updateData)) {
                $category->update($updateData);
            }

            if (array_key_exists('parent_category_id', $data)) {
                if (is_null($data['parent_category_id'])) {
                    $category->hierarchy()->delete();
                } else {
                    $category->hierarchy()->updateOrCreate(
                        ['child_category_id' => $category->id],
                        ['parent_category_id' => $data['parent_category_id']]
                    );
                }
            }

            if (array_key_exists('icon_class', $data)) {
                if (empty($data['icon_class'])) {
                    $category->icon()->delete();
                } else {
                    $category->icon()->updateOrCreate(
                        ['category_id' => $category->id],
                        ['icon_class' => $data['icon_class']]
                    );
                }
            }

            $image = $data['image'] ?? null;
            if ($image) {
                $path = $image->store('categories', 'public');

                $oldMedia = $category->media()->where('is_primary', true)->first();
                if ($oldMedia) {
                    Storage::disk('public')->delete($oldMedia->file_path);
                    $oldMedia->delete();
                }

                $category->media()->create([
                    'file_path' => $path,
                    'file_type' => FileType::Image,
                    'is_primary' => true,
                ]);
            }

            return $category->refresh()->load(['hierarchy', 'icon', 'media']);
        });
    }

    public function deleteCategory(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $media = $category->media()->get();
            foreach ($media as $item) {
                Storage::disk('public')->delete($item->file_path);
                $item->delete();
            }
            $category->hierarchy()->delete();
            $category->icon()->delete();
            $category->delete();
        });
    }

    public function approveCategory(Category $category): void
    {
        $category->update(['is_active' => true]);
        $category->loadMissing('vendorSubmission.vendor.owner');

        $submission = $category->vendorSubmission;

        if (! $submission) {
            return;
        }

        if ($submission->vendor?->owner) {
            $submission->vendor->owner->notify(new CategoryApprovedNotification($this->displayName($category->name)));
        }

        $submission->delete();
    }

    public function rejectCategory(Category $category): void
    {
        $category->vendorSubmission()->update(['status' => 'rejected']);
    }
}
