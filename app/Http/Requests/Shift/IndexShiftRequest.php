<?php

declare(strict_types=1);

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

// authorize() 未定義 = 全許可（Laravel 11+ の FormRequest デフォルト動作）
class IndexShiftRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to'   => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }
}
