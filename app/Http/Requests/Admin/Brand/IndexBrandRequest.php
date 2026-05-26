<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;

class IndexBrandRequest extends FormRequest
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
        ];
    }
}
