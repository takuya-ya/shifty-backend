<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * ログイン処理
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['認証情報が一致しません。'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'ログインしました。',
            'user' => Auth::user(),
        ]);
    }

    /**
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'ログアウトしました。'
        ]);
    }

    /**
     * 認証済みユーザー情報を返す
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
