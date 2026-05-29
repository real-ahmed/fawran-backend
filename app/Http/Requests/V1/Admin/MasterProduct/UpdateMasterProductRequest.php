<?php

namespace App\Http\Requests\V1\Admin\MasterProduct;

use App\Enums\UnitType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMasterProductRequest extends FormRequest
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
        $id = $this->route('masterProduct')?->id;

        return [
            'name' => 'sometimes|array',
            'name.en' => 'sometimes|string|max:255',
            'name.ar' => 'sometimes|string|max:255',
            'description' => 'sometimes|array',
            'description.en' => 'sometimes|string',
            'description.ar' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'unit_type' => ['sometimes', Rule::enum(UnitType::class)],
            'brand_id' => 'nullable|exists:brands,id',
            'sku_barcode' => 'nullable|string|unique:retail_product_details,sku_barcode,'.$id.',master_product_id',
            'is_active' => 'sometimes|boolean',
            'image' => ['nullable', 'image', 'max:2048'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:2048'],
        ];
    }
}
