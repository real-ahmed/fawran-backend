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
                'delivery_zone' => is_array($this->orderDelivery?->deliveryZone?->name)
                    ? ($this->orderDelivery->deliveryZone->name[app()->getLocale()] ?? $this->orderDelivery->deliveryZone->name['en'] ?? '')
                    : $this->orderDelivery?->deliveryZone?->name,
                'address' => $this->orderDelivery?->address ? [
                    'formatted_address' => $this->orderDelivery->address->formatted_address,
                    'latitude' => $this->orderDelivery->address->latitude,
                    'longitude' => $this->orderDelivery->address->longitude,
                    'building_number' => $this->orderDelivery->address->building_number,
                    'phone' => $this->orderDelivery->address->phone,
                ] : null,
            ]),
            'sub_orders' => $this->whenLoaded('subOrders', fn () => $this->subOrders->map(fn ($sub) => [
                'id' => $sub->id,
                'vendor_id' => $sub->vendor_id,
                'vendor_name' => is_array($sub->vendor?->name) ? ($sub->vendor->name[app()->getLocale()] ?? $sub->vendor->name['en'] ?? '') : $sub->vendor?->name,
                'vendor_lat' => $sub->vendor?->latitude,
                'vendor_lng' => $sub->vendor?->longitude,
                'sub_total' => $sub->sub_total,
                'status' => $sub->status,
                'items' => $sub->items?->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => is_array($item->storeItem?->masterProduct?->name)
                        ? ($item->storeItem->masterProduct->name[app()->getLocale()] ?? $item->storeItem->masterProduct->name['en'] ?? '')
                        : $item->storeItem?->masterProduct?->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'options_price' => $item->options_price,
                    'notes' => $item->note?->notes,
                    'options' => $item->options?->map(fn ($opt) => [
                        'option' => is_array($opt->productOption?->name) ? ($opt->productOption->name[app()->getLocale()] ?? $opt->productOption->name['en'] ?? '') : $opt->productOption?->name,
                        'value' => is_array($opt->productOptionValue?->name) ? ($opt->productOptionValue->name[app()->getLocale()] ?? $opt->productOptionValue->name['en'] ?? '') : $opt->productOptionValue?->name,
                        'additional_price' => $opt->additional_price,
                    ]),
                ]),
            ])),
            'courier' => $this->whenLoaded('delivery', fn () => $this->delivery ? [
                'id' => $this->delivery->courier_id,
                'name' => $this->delivery->courier?->user?->name,
                'phone' => $this->delivery->courier?->user?->phone,
                'vehicle_type' => $this->delivery->courier?->vehicle_type,
                'status' => $this->delivery->status,
                'latitude' => $this->delivery->courier?->location?->latitude,
                'longitude' => $this->delivery->courier?->location?->longitude,
            ] : null),
            'status_logs' => $this->whenLoaded('statusLogs', fn () => $this->statusLogs->map(fn ($log) => [
                'from_status' => $log->from_status,
                'to_status' => $log->to_status,
                'changed_by_name' => $log->changedBy?->name ?? 'System',
                'created_at' => $log->created_at,
            ])),
            'delivery_path' => [], // To be implemented with Redis in the future
            'payments' => $this->whenLoaded('payments'),
            'payments_count' => $this->whenLoaded('payments', fn () => $this->payments->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
