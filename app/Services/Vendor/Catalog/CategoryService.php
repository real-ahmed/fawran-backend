<?php

namespace App\Services\Vendor\Catalog;

use App\DTOs\Vendor\Catalog\Category\CategorySubmissionDTO;
use App\Models\Catalog\Category;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function submit(CategorySubmissionDTO $dto, Vendor $vendor): Category
    {
        return DB::transaction(function () use ($dto, $vendor): Category {
            $category = Category::create([
                'name' => $dto->name,
                'is_active' => false,
            ]);

            if ($dto->parent_category_id !== null) {
                $category->hierarchy()->create([
                    'parent_category_id' => $dto->parent_category_id,
                ]);
            }

            if (! empty($dto->icon_class)) {
                $category->icon()->create([
                    'icon_class' => $dto->icon_class,
                ]);
            }

            $category->vendorSubmission()->create([
                'vendor_id' => $vendor->id,
                'status' => 'pending',
            ]);

            return $category->load(['hierarchy', 'icon']);
        });
    }
}
