<?php

namespace App\Http\Requests\V1\Vendor\Role;

use App\Http\Requests\V1\Vendor\Concerns\ResolvesVendorContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorRoleRequest extends FormRequest
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
        $vendorId = $this->vendorId();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique name per store
                Rule::unique('roles', 'name')->where('vendor_id', $vendorId),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ];
    }
}
