<?php

namespace App\Http\Requests\V1\Admin\Vendor;

use App\Enums\VendorStatus;
use App\Enums\VendorType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorRequest extends FormRequest
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
            'owner_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:vendors,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:vendors,phone'],
            'formatted_address' => ['required', 'string', 'max:1000'],
            'type' => ['required', Rule::enum(VendorType::class)],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::enum(VendorStatus::class)],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
