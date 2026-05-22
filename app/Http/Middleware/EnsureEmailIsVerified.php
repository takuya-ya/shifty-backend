<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponsePayload;
use App\Http\Responses\ApiResponseStatus;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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
            status: ApiResponseStatus::Error,
            message: __('auth.email_unverified'),
        );

        if (
            ! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                ! $request->user()->hasVerifiedEmail())
        ) {
            return response()->json($payload, 403);
        }

        return $next($request);
    }
}
