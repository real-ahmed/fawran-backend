<?php

namespace App\Http\Requests\V1\Admin\Customer;

use Illuminate\Foundation\Http\FormRequest;

class IndexCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array{search?: string, is_active?: bool, per_page?: int}
     */
    public function filters(): array
    {
        $filters = $this->validated();

        if ($this->has('is_active')) {
            $filters['is_active'] = $this->boolean('is_active');
        }

        if ($this->filled('per_page')) {
            $filters['per_page'] = (int) $filters['per_page'];
        }

        return $filters;
    }
}
