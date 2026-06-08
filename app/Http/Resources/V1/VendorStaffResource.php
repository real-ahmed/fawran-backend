<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorStaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
                'is_active' => $this->user?->is_active,
            ]),
            'roles' => $this->when(
                $this->relationLoaded('user') && $this->user?->relationLoaded('roles'),
                fn () => VendorRoleResource::collection($this->user->roles->where('vendor_id', $this->vendor_id))
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
