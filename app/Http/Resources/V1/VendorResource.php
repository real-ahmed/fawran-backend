<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
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
            'owner_id' => $this->owner_id,
            'name' => $this->name, // JSON array of translations
            'type' => $this->type?->value, // Enum value
            'status' => $this->status?->value, // Enum value
            'email' => $this->email,
            'phone' => $this->phone,
            'formatted_address' => $this->formatted_address,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'is_active' => (bool) $this->is_active,
            'image' => $this->whenLoaded('media', function () {
                return $this->image;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
