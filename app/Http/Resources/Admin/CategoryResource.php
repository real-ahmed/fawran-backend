<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'is_active' => $this->is_active,
            'parent_category_id' => $this->whenLoaded('hierarchy', fn () => $this->hierarchy?->parent_category_id),
            'icon_class' => $this->whenLoaded('icon', fn () => $this->icon?->icon_class),
            'image' => $this->whenLoaded('media', fn () => $this->image, null),
        ];
    }
}
