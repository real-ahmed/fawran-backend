<?php

namespace App\Http\Requests\V1\Admin\DeliveryZone;

use App\Enums\VehicleType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'vehicle_fees' => ['sometimes', 'array'],
            'vehicle_fees.*.vehicle_type' => ['required_with:vehicle_fees', 'string', Rule::enum(VehicleType::class)],
            'vehicle_fees.*.base_delivery_fee' => ['required_with:vehicle_fees', 'numeric', 'min:0'],
            'vehicle_fees.*.fee_per_km' => ['required_with:vehicle_fees', 'numeric', 'min:0'],
            'vehicle_fees.*.intra_zone_flat_fee' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
