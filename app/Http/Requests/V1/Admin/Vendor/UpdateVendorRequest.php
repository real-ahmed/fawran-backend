<?php

namespace App\Http\Requests\V1\Admin\Vendor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vendorId = $this->route('vendor')?->id ?? null;

        return [
            'owner_id' => ['nullable', 'exists:users,id'],
            'name' => ['nullable', 'array'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:vendors,email,' . $vendorId],
            'phone' => ['nullable', 'string', 'max:20', 'unique:vendors,phone,' . $vendorId],
            'formatted_address' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\VendorType::class)],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
