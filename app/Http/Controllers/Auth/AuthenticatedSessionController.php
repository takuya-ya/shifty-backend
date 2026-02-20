<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * 認証サービスをDIで注入
     *
     * @param AuthService $authService 認証関連のサービス
     */
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * 認証リクエストを処理する
     *
     * @param LoginRequest $request ログインリクエスト
     * @return Response レスポンス（成功時は204 No Content）
     */
    public function store(LoginRequest $request): Response
    {
        $this->authService->login($request);
        return response()->noContent();
    }

    /**
     * 認証済みセッションを破棄する
     *
     * @param Request $request リクエスト
     * @return Response レスポンス（成功時は204 No Content）
     */
    public function destroy(Request $request): Response
    {
        $this->authService->logout($request);
        return response()->noContent();
    }
}
