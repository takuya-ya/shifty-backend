<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthService
{
    /**
     * 認証処理
     *
     * @param LoginRequest $request
     * @return void
     */
    public function login(LoginRequest $request): void
    {
        $request->authenticate();

        $request->session()->regenerate();
    }

    /**
     * ログアウト処理
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }
}
