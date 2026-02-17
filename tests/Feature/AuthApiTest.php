<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーの作成
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * ログインが成功することを確認
     */
    public function test_user_can_login_with_correct_credentials()
    {
        $response = $this->withHeaders([
            'Referer' => 'http://localhost:5173',
        ])->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'ログインしました。',
            ])
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'message',
            ]);

        $this->assertAuthenticatedAs(User::where('email', 'test@example.com')->first());
    }

    /**
     * 間違ったパスワードでログインに失敗することを確認
     */
    public function test_user_cannot_login_with_incorrect_credentials()
    {
        $response = $this->withHeaders([
            'Referer' => 'http://localhost:5173',
        ])->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    }

    /**
     * 認証済みユーザーが自分の情報を取得できることを確認
     */
    public function test_authenticated_user_can_get_their_info()
    {
        $user = User::where('email', 'test@example.com')->first();

        $response = $this->actingAs($user)
            ->withHeaders([
                'Referer' => 'http://localhost:5173',
            ])
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'email' => 'test@example.com',
            ]);
    }

    /**
     * ログアウトが成功することを確認
     */
    public function test_user_can_logout()
    {
        $user = User::where('email', 'test@example.com')->first();

        $response = $this->actingAs($user)
            ->withHeaders([
                'Referer' => 'http://localhost:5173',
            ])
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'ログアウトしました。']);
    }
}
