<?php

namespace App\Http\Requests\Admin\Settlement;

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
        ];
    }
}
