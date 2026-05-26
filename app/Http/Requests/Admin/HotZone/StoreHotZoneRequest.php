<?php

namespace App\Http\Requests\Admin\HotZone;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHotZoneRequest extends FormRequest
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
            'center_latitude' => 'required|numeric|between:-90,90',
            'center_longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:100',
            'intensity' => 'required|string|in:low,medium,high',
            'is_active' => 'sometimes|boolean',
            'starts_at' => 'sometimes|date',
            'name' => 'sometimes|array',
        ];
    }
}
