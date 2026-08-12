<?php

declare(strict_types=1);

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// authorize() 未定義 = 全許可（Laravel 11+ の FormRequest デフォルト動作）
class UpdateShiftRequest extends FormRequest
{
    public const MEMO_MAX_LENGTH = 1000;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'start_at' => [
                'sometimes',
                'date_format:Y-m-d H:i:s',
                Rule::unique('shifts', 'start_at')->where(
                    fn ($query) => $query->where('staff_id', $this->route('shift')->staff_id),
                )->ignore($this->route('shift')),
            ],
            'end_at' => ['sometimes', 'date_format:Y-m-d H:i:s', 'after:start_at'],
            'break_start_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'required_with:break_end_at', 'after_or_equal:start_at', 'before:end_at'],
            'break_end_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'required_with:break_start_at', 'after:break_start_at', 'before_or_equal:end_at'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'memo' => ['nullable', 'string', 'max:' . self::MEMO_MAX_LENGTH],
        ];
    }
}
