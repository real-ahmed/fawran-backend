<?php

namespace App\Http\Requests\V1\Vendor\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('staff')->user_id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => ['sometimes', 'string', 'max:20', 'unique:users,phone,'.$userId],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_ids' => ['array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
