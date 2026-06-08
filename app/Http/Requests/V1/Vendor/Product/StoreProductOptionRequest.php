<?php

namespace App\Http\Requests\V1\Vendor\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'is_required' => ['boolean'],
            'max_selections' => ['integer', 'min:1'],

            'values' => ['required', 'array', 'min:1'],
            'values.*.name' => ['required', 'array'],
            'values.*.name.en' => ['required', 'string', 'max:255'],
            'values.*.name.ar' => ['required', 'string', 'max:255'],
            'values.*.additional_price' => ['numeric', 'min:0'],
            'values.*.is_available' => ['boolean'],
        ];
    }
}
