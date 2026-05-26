<?php

namespace App\Http\Requests\Admin\PayoutRequest;

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
        ];
    }
}
