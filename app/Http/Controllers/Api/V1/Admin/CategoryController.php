<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Catalog\Category;
use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        return CategoryResource::collection($this->categoryService->listCategories($request->search));
    }

    /**
     * Create Category
     *
     * Store a newly created category, including its parent relation and icon.
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());

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
        $category->load(['hierarchy', 'icon']);

        return $this->successResponse(new CategoryResource($category));
    }

    /**
     * Update Category
     *
     * Modify a category's details, parent assignment, or icon.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category = $this->categoryService->updateCategory($category, $request->validated());

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
}
