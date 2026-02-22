<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasswordResetLinkController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {}

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = $this->passwordResetService->sendResetLink(
            $request->only('email')
        );

        return response()->json(['status' => $status]);
    }
}
