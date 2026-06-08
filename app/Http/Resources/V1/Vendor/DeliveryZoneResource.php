<?php

namespace App\Http\Resources\V1\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'delivery_zone_id' => $this->delivery_zone_id,
            'min_order_amount' => (float) $this->min_order_amount,
            'estimated_delivery_time' => (int) $this->estimated_delivery_time,
            'zone' => $this->whenLoaded('deliveryZone', function () {
                return [
                    'id' => $this->deliveryZone->id,
                    'name' => $this->deliveryZone->name,
                    'is_active' => $this->deliveryZone->is_active,
                ];
            }),
        ];
    }
}
