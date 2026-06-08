<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\Catalog\CategoryDataDTO;
use App\DTOs\Admin\Catalog\CategoryFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Category\IndexCategoryRequest;
use App\Http\Requests\V1\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\V1\Admin\Category\UpdateCategoryRequest;
use App\Http\Resources\V1\Admin\CategoryResource;
use App\Models\Catalog\Category;
use App\Services\Admin\Catalog\CategoryService;

/**
 * @group Admin - Categories
 *
 * APIs for managing the catalog category tree, including hierarchy and icons.
 */
class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService) {}

    /**
     * List Categories
     *
     * Get a paginated list of all categories with their hierarchy and icon details.
     */
    public function index(IndexCategoryRequest $request)
    {
        $dto = CategoryFilterDTO::fromRequest($request);
        $categories = $this->categoryService->listCategories($dto);

        return $this->paginatedResponse($categories, CategoryResource::collection($categories->items()));
    }

    /**
     * Create Category
     *
     *  a newly created category, including its parent relation and icon.
     */
    public function store(StoreCategoryRequest $request)
    {
        $dto = CategoryDataDTO::fromRequest($request);
        $category = $this->categoryService->createCategory($dto);

        return $this->successResponse(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    /**
     * Get Category Details
     *
     * Retrieve a specific category by ID with its parent ID and icon class.
     */
    public function show(Category $category)
    {
        return $this->successResponse(new CategoryResource($this->categoryService->getCategory($category)));
    }

    /**
     * Update Category
     *
     * Modify a category's details, parent assignment, or icon.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $dto = CategoryDataDTO::fromRequest($request);
        $category = $this->categoryService->updateCategory($category, $dto);

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    /**
     * Delete Category
     *
     * Remove the specified category and its extension relations (hierarchy, icons).
     */
    public function destroy(Category $category)
    {
        $this->categoryService->deleteCategory($category);

        return $this->successResponse(null, 'Category deleted successfully');
    }

    /**
     * Approve Category
     *
     * Approve a vendor-submitted category and notify the vendor.
     */
    public function approve(Category $category)
    {
        $this->categoryService->approveCategory($category);

        return $this->successResponse(null, __('messages.category_approved_successfully'));
    }

    /**
     * Reject Category
     *
     * Reject a vendor-submitted category.
     */
    public function reject(Category $category)
    {
        $this->categoryService->rejectCategory($category);

        return $this->successResponse(null, __('messages.category_rejected_successfully'));
    }
}
