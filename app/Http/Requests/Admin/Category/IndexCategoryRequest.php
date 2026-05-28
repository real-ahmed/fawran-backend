<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class IndexCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|max:255',
            'approval_status' => 'sometimes|string|in:pending,approved,rejected',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
