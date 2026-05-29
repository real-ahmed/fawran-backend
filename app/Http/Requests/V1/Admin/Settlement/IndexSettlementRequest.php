<?php

namespace App\Http\Requests\V1\Admin\Settlement;

use Illuminate\Foundation\Http\FormRequest;

class IndexSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settlement_type' => 'sometimes|string|in:courier,store',
            'status' => 'sometimes|string|in:pending,completed,disputed',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
