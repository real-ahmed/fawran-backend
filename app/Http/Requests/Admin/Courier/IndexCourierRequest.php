<?php

namespace App\Http\Requests\Admin\Courier;

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
        ];
    }
}
