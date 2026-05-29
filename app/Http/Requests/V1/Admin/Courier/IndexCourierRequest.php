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
            'approval_status' => 'sometimes|string|in:pending,approved,rejected',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
