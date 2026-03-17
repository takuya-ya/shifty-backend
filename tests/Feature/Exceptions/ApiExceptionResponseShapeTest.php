<?php

declare(strict_types=1);

namespace Tests\Feature\Exceptions;

use App\Http\Requests\Test\TestFormRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ApiExceptionResponseShapeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('api')->prefix('api/v1/_test-exceptions')->group(function (): void {
            Route::post('/422-validate', function (Request $request): void {
                $request->validate([
                    'email' => 'required|email',
                ]);
            });

            Route::post('/422-form-request', function (TestFormRequest $request): void {
                // Validation handled by FormRequest
            });

            Route::get('/422', function (): void {
                throw ValidationException::withMessages([
                    'email' => ['The email field is required.'],
                ]);
            });

            Route::get('/403', function (): void {
                throw new AuthorizationException('Forbidden.');
            });

            Route::get('/500', function (): void {
                throw new RuntimeException('Unexpected error.');
            });

            Route::get('/401', function (): string {
                return 'ok';
            })->middleware('auth:sanctum');
        });
    }

    public function test_response_shape_is_same_for_422(): void
    {
        $this->assertCommonErrorShape($this->get('/api/v1/_test-exceptions/422'), 422);
    }

    public function test_response_shape_is_consistent_between_validate_and_form_request(): void
    {
        // 1. validate() 経由
        $response1 = $this->postJson('/api/v1/_test-exceptions/422-validate', []);
        $this->assertCommonErrorShape($response1, 422);
        $response1->assertJsonPath('message', 'Validation failed')
            ->assertJsonPath('errors.email', ['The email field is required.']);

        // 2. FormRequest 経由
        $response2 = $this->postJson('/api/v1/_test-exceptions/422-form-request', []);
        $this->assertCommonErrorShape($response2, 422);
        $response2->assertJsonPath('message', 'Validation failed')
            ->assertJsonPath('errors.email', ['The email field is required.']);
    }

    /**
     * @see ApiExceptionRenderer::normalizeValidationErrors
     */
    public function test_validation_errors_are_always_normalized_to_arrays(): void
    {
        // 手動で文字列のメッセージを持つ ValidationException を投げる
        Route::get('/api/v1/_test-exceptions/422-string-error', function (): void {
            throw ValidationException::withMessages([
                'field1' => 'Single string error message',
                'field2' => ['Array message 1', 'Array message 2'],
            ]);
        });

        $response = $this->getJson('/api/v1/_test-exceptions/422-string-error');

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonPath('errors.field1', ['Single string error message']) // 配列に正規化されていること
            ->assertJsonPath('errors.field2', ['Array message 1', 'Array message 2']);
    }

    public function test_response_shape_is_same_for_401(): void
    {
        $this->assertCommonErrorShape($this->get('/api/v1/_test-exceptions/401'), 401);
    }

    public function test_response_shape_is_same_for_403(): void
    {
        $this->assertCommonErrorShape($this->get('/api/v1/_test-exceptions/403'), 403);
    }

    public function test_response_shape_is_same_for_404(): void
    {
        $this->assertCommonErrorShape($this->get('/api/v1/_test-exceptions/not-found'), 404);
    }

    public function test_response_shape_is_same_for_500(): void
    {
        $this->assertCommonErrorShape($this->get('/api/v1/_test-exceptions/500'), 500);
    }

    private function assertCommonErrorShape($response, int $statusCode): void
    {
        $response->assertStatus($statusCode)
            ->assertJsonStructure([
                'status',
                'data',
                'message',
                'errors',
            ])
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data', null);
    }
}
