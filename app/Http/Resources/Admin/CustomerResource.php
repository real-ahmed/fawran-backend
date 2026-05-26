<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'wallet_balance' => $this->whenLoaded('wallet', fn () => $this->wallet?->balance),
            'addresses_count' => $this->whenLoaded('addresses', fn () => $this->addresses->count()),
            'created_at' => $this->created_at,
        ];
    }
}
