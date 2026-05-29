<?php

namespace App\Http\Requests\V1\Admin\Order;

use App\Enums\AdminPermission;
use Illuminate\Foundation\Http\FormRequest;

class AssignCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api_admin')->can(AdminPermission::ASSIGN_COURIER_TO_ORDER->value);
    }

    public function rules(): array
    {
        return [
            'courier_id' => 'required|integer|exists:couriers,id',
        ];
    }
}
