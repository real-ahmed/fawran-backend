<?php

namespace App\Http\Requests\V1\Vendor\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkingHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'working_hours' => ['required', 'array', 'min:1'],
            'working_hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'working_hours.*.open_time' => ['required', 'date_format:H:i'],
            'working_hours.*.close_time' => ['required', 'date_format:H:i', 'after:working_hours.*.open_time'],
        ];
    }
}
