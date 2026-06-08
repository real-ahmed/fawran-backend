<?php

namespace App\Http\Resources\V1;

use App\Services\Vendor\VendorTypeRegistry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $extras = [];

        if ($this->relationLoaded('store') && $this->store) {
            $handler = app(VendorTypeRegistry::class)->handler($this->store->type);
            $extras = $handler->transformItemExtras($this->resource);
        }

        return array_merge([
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'master_product_id' => $this->master_product_id,
            'price' => (float) $this->price,
            'is_available' => $this->is_available,
            'master_product' => $this->whenLoaded('masterProduct', fn () => [
                'id' => $this->masterProduct?->id,
                'name' => $this->masterProduct?->name,
                'category' => $this->masterProduct?->relationLoaded('category') && $this->masterProduct->category ? [
                    'id' => $this->masterProduct->category->id,
                    'name' => $this->masterProduct->category->name,
                ] : null,
            ]),
        ], $extras);
    }
}
