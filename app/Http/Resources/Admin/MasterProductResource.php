<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'unit_type' => $this->unit_type,
            'is_active' => $this->is_active,
            'description' => $this->whenLoaded('description', fn () => $this->description?->description),
            'retail_detail' => $this->whenLoaded('retailDetail', fn () => $this->retailDetail ? [
                'brand_id' => $this->retailDetail->brand_id,
                'sku_barcode' => $this->retailDetail->sku_barcode,
            ] : null),
            'created_at' => $this->created_at,
        ];
    }
}
