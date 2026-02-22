<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function __construct(
        private readonly EmailVerificationService $emailVerificationService
    ) {}

    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse
    {
        $status = $this->emailVerificationService->sendNotification($request);

        return $status === 'already-verified'
            ? response()->json(['status' => 'already-verified'])
            : response()->json(['status' => 'verification-link-sent']);
    }
}
