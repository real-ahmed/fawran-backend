<?php

namespace App\Http\Requests\V1\Admin\PayoutRequest;

use Illuminate\Foundation\Http\FormRequest;

class IndexPayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|string|in:pending,transferred,rejected',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
