<?php

namespace App\Http\Requests\Admin\SystemSetting;

use Illuminate\Foundation\Http\FormRequest;

class IndexSystemSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group' => 'sometimes|string|max:255',
        ];
    }
}
