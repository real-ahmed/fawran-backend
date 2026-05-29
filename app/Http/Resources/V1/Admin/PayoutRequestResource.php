<?php

namespace App\Http\Resources\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'amount' => $this->amount,
            'status' => $this->status,
            'execution' => $this->whenLoaded('execution', fn () => $this->execution ? [
                'admin_id' => $this->execution->admin_id,
                'executed_at' => $this->execution->executed_at,
            ] : null),
            'created_at' => $this->created_at,
        ];
    }
}
