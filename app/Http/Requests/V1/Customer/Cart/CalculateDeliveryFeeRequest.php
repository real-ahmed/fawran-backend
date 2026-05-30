<?php

namespace App\Http\Requests\V1\Customer\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CalculateDeliveryFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any customer can calculate
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'vendor_ids' => ['required', 'array', 'min:1'],
            'vendor_ids.*' => ['required', 'integer', 'exists:vendors,id'],
        ];
    }
}
