<?php

namespace App\Http\Requests\Admin\AdminUser;

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
        ];
    }
}
