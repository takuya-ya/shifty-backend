<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'staff_id'    => $this->staff_id,
            'start_at'    => $this->start_at->toIso8601String(),
            'end_at'      => $this->end_at->toIso8601String(),
            'shift_state' => $this->shift_state,
            'position'    => $this->whenLoaded('position', fn () => [
                'name' => $this->position->name,
            ]),
            'memo'        => $this->memo,
            'staff_profile' => $this->whenLoaded('staffProfile', fn () => [
                'name' => $this->staffProfile->name,
            ]),
        ];
    }
}
