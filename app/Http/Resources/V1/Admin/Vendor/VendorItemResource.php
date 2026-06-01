<?php

namespace App\Http\Resources\V1\Admin\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorItemResource extends JsonResource
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
            'price' => (float) $this->price,
            'is_available' => $this->is_available,
            'master_product' => new \App\Http\Resources\V1\Admin\MasterProductResource($this->whenLoaded('masterProduct')),
        ];
    }
}
