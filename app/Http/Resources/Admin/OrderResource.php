<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_type' => $this->order_type,
            'total_products' => $this->total_products,
            'status' => $this->status,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer?->customer?->id,
                'name' => $this->customer?->customer?->name,
                'phone' => $this->customer?->customer?->phone,
            ]),
            'delivery_info' => $this->whenLoaded('orderDelivery', fn () => [
                'total_delivery_fee' => $this->orderDelivery?->total_delivery_fee,
                'delivery_zone' => $this->orderDelivery?->deliveryZone?->name,
            ]),
            'sub_orders' => $this->whenLoaded('subOrders', fn () => $this->subOrders->map(fn ($sub) => [
                'id' => $sub->id,
                'vendor_id' => $sub->vendor_id,
                'vendor_name' => $sub->vendor?->name,
                'sub_total' => $sub->sub_total,
                'status' => $sub->status,
                'items_count' => $sub->items?->count() ?? 0,
            ])),
            'courier' => $this->whenLoaded('delivery', fn () => $this->delivery ? [
                'name' => $this->delivery->courier?->user?->name,
                'status' => $this->delivery->status,
            ] : null),
            'payments_count' => $this->whenLoaded('payments', fn () => $this->payments->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
