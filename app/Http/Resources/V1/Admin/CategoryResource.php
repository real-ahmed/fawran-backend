<?php

namespace App\Http\Resources\V1\Admin;

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
            'parent' => $this->whenLoaded('hierarchy', fn () => $this->hierarchy?->relationLoaded('parent') && $this->hierarchy->parent ? [
                'id' => $this->hierarchy->parent->id,
                'name' => $this->hierarchy->parent->name,
            ] : null),
            'icon' => $this->whenLoaded('icon', fn () => $this->icon?->icon_url),
            'image' => $this->whenLoaded('media', fn () => $this->image, null),
        ];
    }
}
