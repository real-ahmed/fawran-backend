<?php

namespace App\Http\Requests\V1\Vendor\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'array'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'is_required' => ['sometimes', 'boolean'],
            'max_selections' => ['sometimes', 'integer', 'min:1'],

            'values' => ['sometimes', 'array', 'min:1'],
            'values.*.id' => ['sometimes', 'nullable', 'integer', 'exists:product_option_values,id'],
            'values.*.name' => ['required_with:values', 'array'],
            'values.*.name.en' => ['required_with:values.*.name', 'string', 'max:255'],
            'values.*.name.ar' => ['required_with:values.*.name', 'string', 'max:255'],
            'values.*.additional_price' => ['sometimes', 'numeric', 'min:0'],
            'values.*.is_available' => ['sometimes', 'boolean'],
            'values.*.is_deleted' => ['sometimes', 'boolean'],
        ];
    }
}
