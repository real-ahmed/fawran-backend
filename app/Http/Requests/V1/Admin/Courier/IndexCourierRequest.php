<?php

namespace App\Http\Requests\V1\Admin\Courier;

use Illuminate\Foundation\Http\FormRequest;

class IndexCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|max:255',
            'is_online' => 'sometimes|boolean',
            'is_blocked' => 'sometimes|boolean',
            'vehicle_type' => 'sometimes|string|in:motorcycle,bicycle,car',
            'delivery_zone_id' => 'sometimes|integer|exists:delivery_zones,id',
            'approval_status' => 'sometimes|string|in:pending,approved,rejected',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
