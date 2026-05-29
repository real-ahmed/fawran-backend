<?php

namespace App\Http\Resources\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettlementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'settlement_type' => $this->settlement_type,
            'target_id' => $this->target_id,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'total_gross' => $this->total_gross,
            'total_deductions' => $this->total_deductions,
            'total_net_exchange' => $this->total_net_exchange,
            'status' => $this->status,
            'items_count' => $this->whenLoaded('items', fn () => $this->items->count()),
            'execution' => $this->whenLoaded('execution', fn () => $this->execution ? [
                'admin_id' => $this->execution->admin_id,
                'execution_method' => $this->execution->execution_method,
                'executed_at' => $this->execution->executed_at,
            ] : null),
            'notes' => $this->whenLoaded('note', fn () => $this->note?->notes),
            'created_at' => $this->created_at,
        ];
    }
}
