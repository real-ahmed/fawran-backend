<?php

namespace App\Http\Requests\V1\Vendor\DeliveryZone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_order_amount' => ['sometimes', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
