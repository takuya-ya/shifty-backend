<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Viteプロキシ経由でLaravelが受け取るホスト名でURLを生成する
        URL::forceRootUrl('http://backend');

        // メール認証リンクをフロントエンドのURLに変更
        VerifyEmail::createUrlUsing(function (object $notifiable) {
            // http://backend/api/verify-email/... で署名生成（Viteプロキシ経由と一致）
            $signedUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
            );

            // パスとクエリ部分だけ取り出してフロントに渡す
            $parsed = parse_url($signedUrl);
            $pathWithQuery = ($parsed['path'] ?? '') . '?' . ($parsed['query'] ?? '');

            $frontendUrl = config('app.frontend_url');
            return "{$frontendUrl}/?verify_path=" . urlencode($pathWithQuery);
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
