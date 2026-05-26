<?php

namespace App\Http\Requests\Admin\HotZone;

use Illuminate\Foundation\Http\FormRequest;

class IndexHotZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => 'sometimes|boolean',
            'intensity' => 'sometimes|string|in:low,medium,high',
        ];
    }
}
