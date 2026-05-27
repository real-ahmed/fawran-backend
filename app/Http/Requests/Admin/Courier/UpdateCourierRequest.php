<?php

namespace App\Http\Requests\Admin\Courier;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourierRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courier = $this->route('courier');

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,'.($courier->user_id ?? 'NULL')],
            'national_id' => ['required', 'string', 'unique:couriers,national_id,'.($courier->id ?? 'NULL')],
            'vehicle_type' => ['required', 'string', 'in:motorcycle,bicycle,car'],
            'plate_number' => ['nullable', 'string', 'max:20'],
        ];
    }
}
