<?php

namespace App\Http\Requests\V1\Customer\Order;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_type' => ['required', 'string', Rule::in(array_column(OrderType::cases(), 'value'))],
            'payment_method' => ['required', 'string', Rule::in(array_column(PaymentMethod::cases(), 'value'))],
            'address_id' => [
                Rule::requiredIf(fn () => $this->input('order_type') === OrderType::Delivery->value),
                'nullable',
                'integer',
                'exists:user_addresses,id',
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.vendor_item_id' => ['required', 'integer', 'exists:vendor_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'items.*.option_value_ids' => ['nullable', 'array'],
            'items.*.option_value_ids.*' => ['integer', 'exists:product_option_values,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
