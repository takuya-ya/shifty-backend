<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/api/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/api/forgot-password', ['email' => $user->email]);

        // 通知からトークンを取得
        $token = '';
        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use (&$token) {
            $token = $notification->token;
            return true;
        });

        $response = $this->post('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newPassword123',
            'password_confirmation' => 'newPassword123',
        ]);

        $response->assertStatus(200);

        $this->assertTrue(
            Auth::attempt([
                'email' => $user->email,
                'password' => 'newPassword123',
            ])
        );
    }
}
