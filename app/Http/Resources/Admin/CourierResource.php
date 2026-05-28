<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),
            'vehicle_type' => $this->vehicle_type,
            'national_id' => $this->national_id,
            'plate_number' => $this->plate_number,
            'is_online' => $this->is_online,
            'document' => $this->whenLoaded('document', fn () => $this->document ? [
                'criminal_record_file' => $this->document->criminal_record_file,
                'contract_number' => $this->document->contract_number,
            ] : null),
            'is_approved' => $this->whenLoaded('approval', fn () => $this->approval !== null, false),
            'approved_at' => $this->whenLoaded('approval', fn () => $this->approval?->approved_at),
            'rejected_at' => $this->rejected_at,
            'location' => $this->whenLoaded('location', fn () => [
                'latitude' => $this->location?->latitude,
                'longitude' => $this->location?->longitude,
                'located_at' => $this->location?->located_at,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
