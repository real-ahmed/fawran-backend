<?php

namespace App\Http\Requests\V1\Vendor\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vendorId = (int) getPermissionsTeamId();

        return [
            'name' => ['sometimes', 'array'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'array'],
            'description.en' => ['required_with:description', 'string', 'max:2000'],
            'description.ar' => ['required_with:description', 'string', 'max:2000'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('vendors', 'email')->ignore($vendorId),
            ],
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('vendors', 'phone')->ignore($vendorId),
            ],
            'formatted_address' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }
}
