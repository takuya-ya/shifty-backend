<?php

declare(strict_types=1);

namespace App\Http\Requests\StaffProfile;

use App\Models\StaffProfile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d'],
            'is_student' => ['required', 'boolean'],
            'hourly_wage' => ['required', 'integer', 'min:0', 'max:'.StaffProfile::HOURLY_WAGE_MAX],
            'memo' => ['nullable', 'string', 'max:'.StaffProfile::MEMO_MAX_LENGTH],
            'position_ids' => ['required', 'array', 'min:1'],
            'position_ids.*' => ['integer', 'distinct', 'exists:positions,id'],
        ];
    }
}
