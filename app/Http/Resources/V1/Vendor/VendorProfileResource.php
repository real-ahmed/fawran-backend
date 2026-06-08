<?php

namespace App\Http\Resources\V1\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorProfileResource extends JsonResource
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
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'status' => $this->status?->value,
            'email' => $this->email,
            'phone' => $this->phone,
            'formatted_address' => $this->formatted_address,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'is_active' => (bool) $this->is_active,
            'average_rating' => $this->whenNotNull($this->average_rating, fn () => (float) $this->average_rating),
            'total_reviews' => $this->whenNotNull($this->total_reviews, fn () => (int) $this->total_reviews),
            'description' => $this->whenLoaded('description', fn () => $this->description?->description),
            'image' => $this->whenLoaded('media', fn () => $this->image),
            'working_hours' => $this->whenLoaded('workingHours'),
            'delivery_zones' => $this->whenLoaded('deliveryZones'),
            'active_subscription' => $this->whenLoaded('activeSubscription', function () {
                $sub = $this->activeSubscription;

                return $sub ? [
                    'id' => $sub->id,
                    'plan_name' => $sub->plan?->name,
                    'starts_at' => $sub->starts_at?->toIso8601String(),
                    'expires_at' => $sub->expires_at?->toIso8601String(),
                    'status' => $sub->status,
                ] : null;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
