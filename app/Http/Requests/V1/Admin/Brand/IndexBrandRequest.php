<?php

namespace App\Http\Requests\V1\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;

class IndexBrandRequest extends FormRequest
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
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
