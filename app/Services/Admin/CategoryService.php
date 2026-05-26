<?php

namespace App\Services\Admin;

use App\Models\Catalog\Category;
use App\Models\Vendor\Vendor;
use App\Notifications\CatalogItemApproved;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    use Paginatable;

    public function listCategories(?string $search = null, ?string $approvalStatus = null)
    {
        return Category::query()
            ->with(['hierarchy', 'icon'])
            ->when($search, function ($query, $search) {
                $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%");
            })
            ->when($approvalStatus, function ($query, $status) {
                $query->whereHas('vendorSubmission', fn ($q) => $q->where('status', $status));
            })
            ->with('vendorSubmission.vendor')
            ->paginate($this->getPerPageLimit());
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

            return $category->load(['hierarchy', 'icon']);
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

            return $category->refresh()->load(['hierarchy', 'icon']);
        });
    }

    public function deleteCategory(Category $category): void
    {
        DB::transaction(function () use ($category) {
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
