<?php

namespace App\Http\Resources\V1\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'sub_total' => (float) $this->sub_total,
            'status' => $this->status->value ?? $this->status,
            'created_at' => $this->created_at,
            'customer' => $this->whenLoaded('order', function () {
                return $this->order->customer && $this->order->customer->customer ? [
                    'name' => $this->order->customer->customer->name,
                    'phone' => $this->order->customer->customer->phone,
                ] : null;
            }),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    $unitPrice = (float) $item->unit_price;
                    $optionsPrice = (float) $item->options_price;
                    $quantity = (float) $item->quantity;

                    return [
                        'id' => $item->id,
                        'name' => is_array($item->vendorItem?->masterProduct?->name)
                            ? ($item->vendorItem->masterProduct->name[app()->getLocale()] ?? $item->vendorItem->masterProduct->name['en'] ?? '')
                            : $item->vendorItem?->masterProduct?->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'options_price' => $optionsPrice,
                        'total_price' => ($unitPrice + $optionsPrice) * $quantity,
                        'notes' => $item->note?->notes,
                        'options' => $item->options?->map(fn ($option) => [
                            'option' => is_array($option->productOption?->name)
                                ? ($option->productOption->name[app()->getLocale()] ?? $option->productOption->name['en'] ?? '')
                                : $option->productOption?->name,
                            'value' => is_array($option->productOptionValue?->name)
                                ? ($option->productOptionValue->name[app()->getLocale()] ?? $option->productOptionValue->name['en'] ?? '')
                                : $option->productOptionValue?->name,
                            'additional_price' => (float) $option->additional_price,
                        ]),
                    ];
                });
            }),
        ];
    }
}
