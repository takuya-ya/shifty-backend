<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'hourly_wage' => $this->hourly_wage,
            'is_student' => $this->is_student,
            'memo'       => $this->memo,
            'positions'  => $this->whenLoaded('positions'),
        ];
    }
}
