<?php

namespace App\Http\Resources\V1\Admin\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'monthly_price' => $this->monthly_price,
            'commission_percentage' => $this->commission_percentage,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
