<?php

namespace App\Http\Requests\Admin\RefundRequest;

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
        ];
    }
}
