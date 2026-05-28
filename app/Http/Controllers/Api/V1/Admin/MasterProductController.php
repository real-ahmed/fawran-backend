<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterProduct\IndexMasterProductRequest;
use App\Http\Requests\Admin\MasterProduct\StoreMasterProductRequest;
use App\Http\Requests\Admin\MasterProduct\UpdateMasterProductRequest;
use App\Http\Resources\Admin\MasterProductResource;
use App\Models\Product\MasterProduct;
use App\Services\Admin\Catalog\MasterProductService;

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
    public function index(IndexMasterProductRequest $request)
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
    public function store(StoreMasterProductRequest $request)
    {
        $product = $this->masterProductService->createProduct($request->validated());

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
    public function update(UpdateMasterProductRequest $request, MasterProduct $masterProduct)
    {
        $product = $this->masterProductService->updateProduct($masterProduct, $request->validated());

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

    /**
     * Approve Master Product
     *
     * Approve a vendor-submitted master product and notify the vendor.
     */
    public function approve(MasterProduct $masterProduct)
    {
        $this->masterProductService->approveProduct($masterProduct);

        return $this->successResponse(null, __('messages.master_product_approved_successfully'));
    }

    /**
     * Reject Master Product
     *
     * Reject a vendor-submitted master product.
     *
     * @bodyParam reason string optional Reason for rejection. Example: Incomplete description
     */
    public function reject(Request $request, MasterProduct $masterProduct)
    {
        $request->validate(['reason' => 'sometimes|string|max:255']);

        $this->masterProductService->rejectProduct($masterProduct, $request->reason);

        return $this->successResponse(null, __('messages.master_product_rejected_successfully'));
    }
}
