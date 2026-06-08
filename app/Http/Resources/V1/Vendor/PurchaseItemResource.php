<?php

namespace App\Http\Resources\V1\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_order_id' => $this->purchase_order_id,
            'vendor_item_id' => $this->vendor_item_id,
            'quantity' => (float) $this->quantity,
            'cost_price' => (float) $this->cost_price,
            'store_item' => $this->whenLoaded('storeItem', fn () => [
                'id' => $this->storeItem?->id,
                'name' => $this->storeItem?->masterProduct?->name,
            ]),
        ];
    }
}
