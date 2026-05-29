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
