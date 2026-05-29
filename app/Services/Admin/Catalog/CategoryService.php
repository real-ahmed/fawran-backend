<?php

namespace App\Services\Admin\Catalog;

use App\DTOs\Admin\Catalog\CategoryDataDTO;
use App\DTOs\Admin\Catalog\CategoryFilterDTO;
use App\Enums\FileType;
use App\Models\Catalog\Category;
use App\Notifications\Catalog\CategoryApprovedNotification;
use App\Traits\Paginatable;
use App\Traits\ResolvesDisplayName;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    use Paginatable, ResolvesDisplayName;

    public function listCategories(CategoryFilterDTO $filters)
    {
        return Category::query()
            ->withListRelations()
            ->searchName($filters->search)
            ->approvalStatus($filters->approval_status)
            ->active($filters->is_active)
            ->withVendorSubmission()
            ->newest()
            ->cursorPaginate($this->getPerPageLimit())
            ->withQueryString();
    }

    public function createCategory(CategoryDataDTO $dto): Category
    {
        return DB::transaction(function () use ($dto) {
            $category = Category::create([
                'name' => $dto->name,
                'is_active' => $dto->is_active ?? true,
            ]);

            if ($dto->has_parent_category_id && ! is_null($dto->parent_category_id)) {
                $category->hierarchy()->create([
                    'parent_category_id' => $dto->parent_category_id,
                ]);
            }

            if ($dto->icon instanceof UploadedFile) {
                $path = $dto->icon->store('category-icons', 'public');
                $category->icon()->create([
                    'icon_path' => $path,
                ]);
            }

            if ($dto->image) {
                $path = $dto->image->store('categories', 'public');
                $category->media()->create([
                    'file_path' => $path,
                    'file_type' => FileType::Image,
                    'is_primary' => true,
                ]);
            }

            return $category->load(['hierarchy', 'icon', 'media']);
        });
    }

    public function getCategory(Category $category): Category
    {
        return $category->load(['hierarchy', 'icon']);
    }

    public function updateCategory(Category $category, CategoryDataDTO $dto): Category
    {
        return DB::transaction(function () use ($dto, $category) {
            $updateData = [];
            if ($dto->name !== null) {
                $updateData['name'] = $dto->name;
            }
            if ($dto->is_active !== null) {
                $updateData['is_active'] = $dto->is_active;
            }

            if (! empty($updateData)) {
                $category->update($updateData);
            }

            if ($dto->has_parent_category_id) {
                if (is_null($dto->parent_category_id)) {
                    $category->hierarchy()->delete();
                } else {
                    $category->hierarchy()->updateOrCreate(
                        ['child_category_id' => $category->id],
                        ['parent_category_id' => $dto->parent_category_id]
                    );
                }
            }

            if ($dto->icon instanceof UploadedFile) {
                $path = $dto->icon->store('category-icons', 'public');
                $oldIcon = $category->icon;
                if ($oldIcon && $oldIcon->icon_path) {
                    Storage::disk('public')->delete($oldIcon->icon_path);
                }
                $category->icon()->updateOrCreate(
                    ['category_id' => $category->id],
                    ['icon_path' => $path]
                );
            } elseif ($dto->has_icon && is_null($dto->icon)) {
                $oldIcon = $category->icon;
                if ($oldIcon && $oldIcon->icon_path) {
                    Storage::disk('public')->delete($oldIcon->icon_path);
                    $oldIcon->delete();
                }
            }

            if ($dto->image) {
                $path = $dto->image->store('categories', 'public');

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
