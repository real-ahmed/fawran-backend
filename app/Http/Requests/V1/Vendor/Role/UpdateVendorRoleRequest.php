<?php

namespace App\Http\Requests\V1\Vendor\Role;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRoleRequest extends FormRequest
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
        $vendorId = $this->header('X-VENDOR-ID') ?? $this->query('vendor_id');
        $roleId = $this->route('role')?->id ?? null;

        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
                // Unique name per store except current role
                Rule::unique('roles', 'name')->where('vendor_id', $vendorId)->ignore($roleId),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ];
    }
}
