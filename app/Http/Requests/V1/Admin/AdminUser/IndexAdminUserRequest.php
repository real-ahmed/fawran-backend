<?php

namespace App\Http\Requests\V1\Admin\AdminUser;

use Illuminate\Foundation\Http\FormRequest;

class IndexAdminUserRequest extends FormRequest
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
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
