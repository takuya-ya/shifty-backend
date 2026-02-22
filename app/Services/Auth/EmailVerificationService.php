<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationService
{
    /**
     * メールアドレスを確認する
     *
     * @param EmailVerificationRequest $request
     * @return bool すでに認証済みだった場合はfalse、新しく認証した場合はtrue
     */
    public function verify(EmailVerificationRequest $request): bool
    {
        if ($request->user()->hasVerifiedEmail()) {
            return false;
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return true;
    }

    /**
     * 確認メールを再送する
     *
     * @param Request $request
     * @return string 結果ステータス ('already-verified' または 'verification-link-sent')
     */
    public function sendNotification(Request $request): string
    {
        if ($request->user()->hasVerifiedEmail()) {
            return 'already-verified';
        }

        $request->user()->sendEmailVerificationNotification();

        return 'verification-link-sent';
    }
}
