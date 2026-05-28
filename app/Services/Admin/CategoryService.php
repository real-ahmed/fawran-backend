<?php

namespace App\Services\Admin;

use App\Enums\FileType;
use App\Models\Catalog\Category;
use App\Models\Vendor\Vendor;
use App\Notifications\CatalogItemApproved;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    use Paginatable;

    public function __construct(protected CatalogRealtimeNotifier $catalogRealtimeNotifier) {}

    public function listCategories(?string $search = null, ?string $approvalStatus = null, ?bool $isActive = null)
    {
        return Category::query()
            ->withListRelations()
            ->searchName($search)
            ->approvalStatus($approvalStatus)
            ->active($isActive)
            ->withVendorSubmission()
            ->paginate($this->getPerPageLimit())
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

    public function proposeCategory(array $data, Vendor $vendor): Category
    {
        return DB::transaction(function () use ($data, $vendor) {
            $category = Category::create([
                'name' => $data['name'],
                'is_active' => false, // Force inactive
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

            $category->vendorSubmission()->create([
                'vendor_id' => $vendor->id,
                'status' => 'pending',
            ]);

            $this->catalogRealtimeNotifier->notifySubmission('Category', $category->id, $category->name, $vendor);

            return $category->load(['hierarchy', 'icon']);
        });
    }

    public function approveCategory(Category $category): void
    {
        $category->update(['is_active' => true]);

        if ($submission = $category->vendorSubmission) {
            $vendor = $submission->vendor;
            if ($vendor && $vendor->owner) {
                // Determine name string based on locales, defaulting to 'en'
                $categoryName = is_array($category->name) ? ($category->name['en'] ?? current($category->name)) : 'Unknown';
                $vendor->owner->notify(new CatalogItemApproved('Category', $categoryName));
            }
            $submission->delete();
        }
    }

    public function rejectCategory(Category $category): void
    {
        if ($submission = $category->vendorSubmission) {
            $submission->update(['status' => 'rejected']);
        }
    }
}
