<?php

namespace App\Http\Requests\V1\Vendor\DeliveryZone;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_zone_id' => ['required', 'integer', 'exists:delivery_zones,id'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['required', 'integer', 'min:1'],
        ];
    }
}
