<?php

declare(strict_types=1);

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// authorize() 未定義 = 全許可（Laravel 11+ の FormRequest デフォルト動作）
class StoreShiftRequest extends FormRequest
{
    /**
     * 印刷・画面表示を保護するためのメモの実用上限（DB の text 上限より厳しい業務上の制約）
     */
    public const MEMO_MAX_LENGTH = 1000;

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
                // DB の UNIQUE は生成カラム unique_delete_key により削除済み行を衝突対象から外すため、
                // バリデーションでも削除済み行を除外して DB の実態に合わせる
                Rule::unique('shifts', 'staff_id')->where(
                    fn ($query) => $query
                        ->where('start_at', $this->input('start_at'))
                        ->whereNull('deleted_at'),
                ),
            ],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
            'break_start_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'required_with:break_end_at', 'after_or_equal:start_at', 'before:end_at'],
            'break_end_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'required_with:break_start_at', 'after:break_start_at', 'before_or_equal:end_at'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'memo' => ['nullable', 'string', 'max:'.self::MEMO_MAX_LENGTH],
        ];
    }
}
