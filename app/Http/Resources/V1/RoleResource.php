<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'display_name' => $this->display_name,
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->map(function ($p) {
                    return [
                        'name' => $p->name,
                        'display_name' => [
                            'en' => trans('permissions.'.$p->name, [], 'en'),
                            'ar' => trans('permissions.'.$p->name, [], 'ar'),
                        ],
                    ];
                });
            }),
        ];
    }
}
