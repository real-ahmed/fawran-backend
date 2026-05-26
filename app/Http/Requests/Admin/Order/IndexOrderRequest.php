<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class IndexOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|string|in:pending,processing,out_for_delivery,delivered,cancelled',
            'order_type' => 'sometimes|string|in:delivery,pickup,in_store',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'customer_id' => 'sometimes|integer|exists:users,id',
            'vendor_id' => 'sometimes|integer|exists:vendors,id',
        ];
    }
}
