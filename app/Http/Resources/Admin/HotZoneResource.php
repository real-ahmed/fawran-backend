<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'center_latitude' => $this->center_latitude,
            'center_longitude' => $this->center_longitude,
            'radius_meters' => $this->radius_meters,
            'intensity' => $this->intensity,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at,
            'name' => $this->whenLoaded('manualHotZone', fn () => $this->manualHotZone?->name),
            'auto_data' => $this->whenLoaded('autoHotZone', fn () => $this->autoHotZone ? [
                'order_count' => $this->autoHotZone->order_count,
                'expires_at' => $this->autoHotZone->expires_at,
            ] : null),
        ];
    }
}
