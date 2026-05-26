<?php

namespace App\Http\Requests\Admin\MasterProduct;

use Illuminate\Foundation\Http\FormRequest;

class IndexMasterProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'brand_id' => 'sometimes|integer|exists:brands,id',
            'is_active' => 'sometimes|boolean',
            'approval_status' => 'sometimes|string|in:pending,approved,rejected',
        ];
    }
}
