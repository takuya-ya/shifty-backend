<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Responses;

use App\Http\Responses\ApiResponsePayload;
use App\Http\Responses\ApiResponseStatus;
use App\Http\Responses\ApiResponseTrait;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * ApiResponseTrait のFeatureテスト。
 *
 * Laravelコンテナが初期化された状態で
 * レスポンスジェネレーション機能を検証します。
 */
class ApiResponseTraitTest extends TestCase
{
    /**
     * success() メソッドが正しい構造の JsonResponse を返すこと
     */
    #[Test]
    public function test_success_returns_json_response_with_success_status(): void
    {
        $controller = new class {
            use ApiResponseTrait;

            public function callSuccess()
            {
                return $this->success(data: ['id' => 1, 'name' => 'Test User']);
            }
        };

        $response = $controller->callSuccess();

        $this->assertEquals(200, $response->status());

        $payload = json_decode($response->getContent(), associative: true);
        $this->assertEquals('success', $payload['status']);
        $this->assertEquals(['id' => 1, 'name' => 'Test User'], $payload['data']);
        $this->assertNull($payload['message']);
        $this->assertNull($payload['errors']);
    }

    /**
     * success() メソッドがメッセージ付きレスポンスを返すこと
     */
    #[Test]
    public function test_success_includes_message_when_provided(): void
    {
        $controller = new class {
            use ApiResponseTrait;

            public function callSuccess()
            {
                return $this->success(
                    data: ['user' => 'created'],
                    message: 'User created successfully',
                    status: 201
                );
            }
        };

        $response = $controller->callSuccess();

        $this->assertEquals(201, $response->status());

        $payload = json_decode($response->getContent(), associative: true);
        $this->assertEquals('success', $payload['status']);
        $this->assertEquals('User created successfully', $payload['message']);
    }

    /**
     * error() メソッドが正しい構造の JsonResponse を返すこと（errors なし）
     */
    #[Test]
    public function test_error_returns_json_response_with_error_status(): void
    {
        $controller = new class {
            use ApiResponseTrait;

            public function callError()
            {
                return $this->error(message: 'Not found', status: 404);
            }
        };

        $response = $controller->callError();

        $this->assertEquals(404, $response->status());

        $payload = json_decode($response->getContent(), associative: true);
        $this->assertEquals('error', $payload['status']);
        $this->assertEquals('Not found', $payload['message']);
        $this->assertNull($payload['data']);
        $this->assertNull($payload['errors']);
    }

    /**
     * error() メソッドがフィールド別エラー詳細を含むこと（422 Unprocessable Entity）
     */
    #[Test]
    public function test_error_includes_validation_errors(): void
    {
        $controller = new class {
            use ApiResponseTrait;

            public function callError()
            {
                $errors = [
                    'name' => 'Name is required',
                    'email' => ['Email is invalid', 'Email is already registered'],
                ];

                return $this->error(
                    message: 'Validation failed',
                    errors: $errors,
                    status: 422
                );
            }
        };

        $response = $controller->callError();

        $this->assertEquals(422, $response->status());

        $payload = json_decode($response->getContent(), associative: true);
        $this->assertEquals('error', $payload['status']);
        $this->assertEquals('Validation failed', $payload['message']);
        $this->assertIsArray($payload['errors']);
        $this->assertEquals('Name is required', $payload['errors']['name']);
    }

    /**
     * ApiResponsePayload が Arrayable インターフェースを実装していること
     */
    #[Test]
    public function test_api_response_payload_is_arrayable(): void
    {
        $payload = new ApiResponsePayload(
            status: ApiResponseStatus::Success,
            data: ['id' => 1],
            message: 'OK',
            errors: null
        );

        $array = $payload->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('success', $array['status']);
        $this->assertEquals(['id' => 1], $array['data']);
        $this->assertEquals('OK', $array['message']);
        $this->assertNull($array['errors']);
    }
}