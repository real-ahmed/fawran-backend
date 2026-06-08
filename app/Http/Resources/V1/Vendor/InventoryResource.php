<?php

namespace App\Http\Resources\V1\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'vendor_item_id' => $this->vendor_item_id,
            'current_stock' => (float) $this->current_stock,
            'low_stock_threshold' => (float) $this->low_stock_threshold,
            'item' => $this->whenLoaded('storeItem', function () {
                $masterProduct = $this->storeItem->relationLoaded('masterProduct')
                    ? $this->storeItem->masterProduct
                    : null;

                return [
                    'id' => $this->storeItem->id,
                    'name' => $masterProduct?->name,
                    'price' => (float) $this->storeItem->price,
                ];
            }),
        ];
    }
}
