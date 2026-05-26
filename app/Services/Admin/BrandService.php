<?php

namespace App\Services\Admin;

use App\Models\Catalog\Brand;
use App\Traits\Paginatable;

class BrandService
{
    use Paginatable;

    public function listBrands(?string $search = null)
    {
        return Brand::query()
            ->when($search, function ($query, $search) {
                $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%");
            })
            ->paginate($this->getPerPageLimit());
    }

    public function createBrand(array $data): Brand
    {
        return Brand::create($data);
    }

    public function updateBrand(Brand $brand, array $data): Brand
    {
        $brand->update($data);

        return $brand;
    }

    public function deleteBrand(Brand $brand): void
    {
        $brand->delete();
    }
}
