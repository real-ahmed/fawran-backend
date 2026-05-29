<?php

namespace App\Http\Requests\V1\Admin\Vendor;

use App\Enums\VendorStatus;
use App\Enums\VendorType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vendorId = $this->route('vendor')?->id ?? null;

        return [
            'owner_id' => ['nullable', 'exists:users,id'],
            'name' => ['nullable', 'array'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:vendors,email,'.$vendorId],
            'phone' => ['nullable', 'string', 'max:20', 'unique:vendors,phone,'.$vendorId],
            'formatted_address' => ['nullable', 'string', 'max:1000'],
            'type' => ['sometimes', Rule::enum(VendorType::class)],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'is_active' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::enum(VendorStatus::class)],
            'image' => ['nullable', 'image', 'max:2048'],

            // Working Hours
            'working_hours' => ['nullable', 'array'],
            'working_hours.*.day_of_week' => ['required_with:working_hours', 'integer', 'between:0,6'],
            'working_hours.*.open_time' => ['required_with:working_hours', 'date_format:H:i'],
            'working_hours.*.close_time' => ['required_with:working_hours', 'date_format:H:i', 'after:working_hours.*.open_time'],

            // Delivery Zones
            'delivery_zones' => ['nullable', 'array'],
            'delivery_zones.*.delivery_zone_id' => ['required_with:delivery_zones', 'exists:delivery_zones,id'],
            'delivery_zones.*.min_order_amount' => ['required_with:delivery_zones', 'numeric', 'min:0'],
            'delivery_zones.*.estimated_delivery_time' => ['required_with:delivery_zones', 'integer', 'min:1'],
        ];
    }
}
