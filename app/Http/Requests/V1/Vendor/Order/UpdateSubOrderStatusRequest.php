<?php

namespace App\Http\Requests\V1\Vendor\Order;

use App\Enums\SubOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(array_column(SubOrderStatus::cases(), 'value'))],
        ];
    }
}
