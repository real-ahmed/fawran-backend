<?php

namespace App\Http\Resources\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'order_id' => $this->order_id,
            'total_amount' => $this->total_amount,
            'reason' => $this->reason,
            'resolution' => $this->resolution,
            'status' => $this->status,
            'items_count' => $this->whenLoaded('items', fn () => $this->items->count()),
            'created_at' => $this->created_at,
        ];
    }
}
