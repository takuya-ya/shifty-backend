<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponsePayload;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $payload = new ApiResponsePayload(
            message: __('auth.email_unverified'),
        );

        // Laravel 標準は MustVerifyEmail 未実装のモデルを検査せず通すが、認証モデルが User のみの
        // 本アプリでは instanceof が常に true になる（PHPStan 指摘）ため外し、全ユーザーを検査する
        if (! $request->user() || ! $request->user()->hasVerifiedEmail()) {
            return response()->json($payload, 403);
        }

        return $next($request);
    }
}
