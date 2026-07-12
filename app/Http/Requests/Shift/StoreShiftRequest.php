<?php

declare(strict_types=1);

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftRequest extends FormRequest
{
    /**
     * 印刷・画面表示を保護するためのメモの実用上限（DB の text 上限より厳しい業務上の制約）
     */
    public const MEMO_MAX_LENGTH = 1000;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'staff_id' => [
                'required',
                'integer',
                'exists:staff_profiles,id',
                // DB の UNIQUE (staff_id, start_at) はソフトデリート行も含むため、
                // バリデーションでも削除済み行を除外せず DB の実態に合わせる
                Rule::unique('shifts', 'staff_id')->where(
                    fn ($query) => $query->where('start_at', $this->input('start_at')),
                ),
            ],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'memo' => ['nullable', 'string', 'max:'.self::MEMO_MAX_LENGTH],
        ];
    }
}
