<?php

declare(strict_types=1);

namespace App\Http\Requests\StaffProfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public const MEMO_MAX_LENGTH = 1000;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'is_student' => ['nullable', 'boolean'],
            'hourly_wage' => ['required', 'integer', 'min:0', 'max:5000'],
            'memo' => ['nullable', 'string', 'max:'.self::MEMO_MAX_LENGTH],
            'position_ids' => ['required', 'array', 'min:1'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
        ];
    }
}
