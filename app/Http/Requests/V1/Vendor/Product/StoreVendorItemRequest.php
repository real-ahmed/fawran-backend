<?php

namespace App\Http\Requests\V1\Vendor\Product;

use App\Http\Requests\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Models\Vendor\Vendor;
use App\Services\Vendor\VendorTypeRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVendorItemRequest extends FormRequest
{
    use ResolvesVendorContext;

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
        $vendor = Vendor::findOrFail($this->vendorId());
        $handler = app(VendorTypeRegistry::class)->handler($vendor->type);

        $rules = [
            'master_product_id' => ['required', 'integer', 'exists:master_products,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['boolean'],
        ];

        return array_merge($rules, $handler->itemValidationRules(isUpdate: false));
    }
}
