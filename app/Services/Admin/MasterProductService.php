<?php

namespace App\Services\Admin;

use App\Models\Product\MasterProduct;
use App\Traits\Paginatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterProductService
{
    use Paginatable;

    public function listProducts(Request $request)
    {
        $query = MasterProduct::with(['category', 'description', 'retailDetail']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('unit_type')) {
            $query->where('unit_type', $request->query('unit_type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest('created_at')->paginate($this->getPerPageLimit());
    }

    public function createProduct(array $data): MasterProduct
    {
        return DB::transaction(function () use ($data) {
            $product = MasterProduct::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'unit_type' => $data['unit_type'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (! empty($data['description'])) {
                $product->description()->create([
                    'description' => $data['description'],
                ]);
            }

            if (! empty($data['brand_id']) && ! empty($data['sku_barcode'])) {
                $product->retailDetail()->create([
                    'brand_id' => $data['brand_id'],
                    'sku_barcode' => $data['sku_barcode'],
                ]);
            }

            return $product->load(['category', 'description', 'retailDetail']);
        });
    }

    public function updateProduct(MasterProduct $product, array $data): MasterProduct
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update(collect($data)->only(['category_id', 'name', 'unit_type', 'is_active'])->toArray());

            if (array_key_exists('description', $data)) {
                if (! empty($data['description'])) {
                    $product->description()->updateOrCreate([], ['description' => $data['description']]);
                } else {
                    $product->description()->delete();
                }
            }

            if (array_key_exists('brand_id', $data) || array_key_exists('sku_barcode', $data)) {
                if (! empty($data['brand_id']) && ! empty($data['sku_barcode'])) {
                    $product->retailDetail()->updateOrCreate([], [
                        'brand_id' => $data['brand_id'],
                        'sku_barcode' => $data['sku_barcode'],
                    ]);
                } else {
                    $product->retailDetail()->delete();
                }
            }

            return $product->load(['category', 'description', 'retailDetail']);
        });
    }

    public function deleteProduct(MasterProduct $product): void
    {
        DB::transaction(function () use ($product) {
            $product->description()->delete();
            $product->retailDetail()->delete();
            $product->delete();
        });
    }
}
