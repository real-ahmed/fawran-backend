<?php

namespace App\Http\Requests\V1\Vendor\Product;

use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMasterProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'unit_type' => ['required', 'string', Rule::in(array_column(UnitType::cases(), 'value'))],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'sku_barcode' => ['nullable', 'string', 'max:255', 'unique:retail_product_details,sku_barcode'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
        ];
    }
}
