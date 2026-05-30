<?php

namespace App\Http\Resources\V1\Admin\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Admin\Finance\SubscriptionPlanResource;

class ExpiringSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'vendor_name' => $this->vendor->name ?? null,
            'owner_name' => $this->vendor->owner->name ?? null,
            'owner_phone' => $this->vendor->owner->phone ?? null,
            'plan_id' => $this->plan_id,
            'plan' => new SubscriptionPlanResource($this->whenLoaded('plan')),
            'status' => $this->status,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
