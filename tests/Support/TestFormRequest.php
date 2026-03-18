<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Foundation\Http\FormRequest;

class TestFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
