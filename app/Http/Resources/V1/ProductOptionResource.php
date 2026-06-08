<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_item_id' => $this->vendor_item_id,
            'name' => $this->name,
            'is_required' => (bool) $this->is_required,
            'max_selections' => (int) $this->max_selections,
            'values' => ProductOptionValueResource::collection($this->whenLoaded('values')),
        ];
    }
}
