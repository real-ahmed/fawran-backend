<?php

namespace App\Http\Requests\V1\Admin\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class IndexVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
            'type' => 'sometimes|string|in:restaurant,grocery,pharmacy',
            'status' => 'sometimes|string|in:online,offline,busy',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
