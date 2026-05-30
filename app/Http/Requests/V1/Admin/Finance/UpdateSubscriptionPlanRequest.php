<?php

namespace App\Http\Requests\V1\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'array'],
            'name.en' => ['required_with:name', 'string', 'max:255'],
            'name.ar' => ['required_with:name', 'string', 'max:255'],
            'monthly_price' => ['sometimes', 'numeric', 'min:0'],
            'commission_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'features' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
