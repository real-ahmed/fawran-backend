<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\MasterProductResource;
use App\Models\Product\MasterProduct;
use App\Services\Admin\MasterProductService;
use Illuminate\Http\Request;

/**
 * @group Admin - Master Products
 *
 * APIs for managing the global product catalog (master products).
 */
class MasterProductController extends Controller
{
    public function __construct(protected MasterProductService $masterProductService) {}

    /**
     * List Master Products
     *
     * Get a paginated list of all master products with optional filters.
     *
     * @queryParam category_id int Filter by category. Example: 1
     * @queryParam unit_type string Filter by unit type (piece, kg, gram, portion). Example: piece
     * @queryParam is_active boolean Filter by active status. Example: 1
     * @queryParam search string Search by product name. Example: Rice
     */
    public function index(Request $request)
    {
        return MasterProductResource::collection($this->masterProductService->listProducts($request));
    }

    /**
     * Create Master Product
     *
     * Store a new master product in the global catalog.
     *
     * @bodyParam name object required Localized name. Example: {"en": "Basmati Rice", "ar": "أرز بسمتي"}
     * @bodyParam category_id int required The category ID. Example: 1
     * @bodyParam unit_type string required The unit type. Example: piece
     * @bodyParam description object optional Localized description. Example: {"en": "Premium rice"}
     * @bodyParam brand_id int optional Brand ID for retail products. Example: 1
     * @bodyParam sku_barcode string optional SKU barcode for retail products. Example: 123456789
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_type' => 'required|string',
            'is_active' => 'sometimes|boolean',
            'description' => 'sometimes|array',
            'brand_id' => 'sometimes|exists:brands,id',
            'sku_barcode' => 'sometimes|string|unique:retail_product_details,sku_barcode',
        ]);

        $product = $this->masterProductService->createProduct($validated);

        return $this->successResponse(
            new MasterProductResource($product),
            __('messages.created_successfully'),
            201
        );
    }

    /**
     * Get Master Product Details
     *
     * Retrieve a specific master product with its category, description, and retail details.
     */
    public function show(MasterProduct $masterProduct)
    {
        return $this->successResponse(
            new MasterProductResource($masterProduct->load(['category', 'description', 'retailDetail']))
        );
    }

    /**
     * Update Master Product
     *
     * Modify an existing master product and its extension tables.
     */
    public function update(Request $request, MasterProduct $masterProduct)
    {
        $validated = $request->validate([
            'name' => 'sometimes|array',
            'name.en' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,id',
            'unit_type' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|array',
            'brand_id' => 'nullable|exists:brands,id',
            'sku_barcode' => 'nullable|string|unique:retail_product_details,sku_barcode,'.$masterProduct->id.',master_product_id',
        ]);

        $product = $this->masterProductService->updateProduct($masterProduct, $validated);

        return $this->successResponse(
            new MasterProductResource($product),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete Master Product
     *
     * Remove a master product and its extension tables from the catalog.
     */
    public function destroy(MasterProduct $masterProduct)
    {
        $this->masterProductService->deleteProduct($masterProduct);

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
