<?php

namespace App\Http\Requests\V1\Admin\RefundRequest;

use Illuminate\Foundation\Http\FormRequest;

class IndexRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|string|in:pending,approved,rejected,processed',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
