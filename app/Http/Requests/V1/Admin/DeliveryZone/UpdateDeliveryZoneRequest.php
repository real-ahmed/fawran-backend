<?php

namespace App\Http\Requests\V1\Admin\DeliveryZone;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryZoneRequest extends FormRequest
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
            'name' => ['sometimes', 'array'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'coordinates' => ['sometimes', 'array', 'min:3'],
            'coordinates.*.lat' => ['required_with:coordinates', 'numeric', 'between:-90,90'],
            'coordinates.*.lng' => ['required_with:coordinates', 'numeric', 'between:-180,180'],
        ];
    }
}
