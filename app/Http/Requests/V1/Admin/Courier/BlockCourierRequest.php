<?php

namespace App\Http\Requests\V1\Admin\Courier;

use Illuminate\Foundation\Http\FormRequest;

class BlockCourierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'is_blocked' => ['required', 'boolean'],
        ];
    }
}
