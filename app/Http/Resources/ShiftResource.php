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
            'start_at'    => $this->start_at,
            'end_at'      => $this->end_at,
            'shift_state' => $this->shift_state,
            'position_id' => $this->position_id,
            'memo'        => $this->memo,
            'staff_profile' => $this->whenLoaded('staffProfile', fn () => [
                'name' => $this->staffProfile->name,
            ]),
        ];
    }
}
