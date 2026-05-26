<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'array'],
            'name.*' => ['required_with:name', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'parent_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'icon_class' => ['nullable', 'string', 'max:255'],
        ];
    }
}
