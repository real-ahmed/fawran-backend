<?php

namespace App\Services\Vendor;

use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Product\MasterProduct;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorCatalogService
{
    public function getCategories(): LengthAwarePaginator
    {
        return Category::where('is_active', true)->paginate(20);
    }

    public function getBrands(): LengthAwarePaginator
    {
        return Brand::where('is_active', true)->paginate(20);
    }

    public function getMasterProducts(array $filters = []): LengthAwarePaginator
    {
        $query = MasterProduct::where('is_active', true)
            ->with(['category', 'description', 'retailDetail']);

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name->en', 'LIKE', "%{$search}%")
                    ->orWhere('name->ar', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate(20);
    }
}
