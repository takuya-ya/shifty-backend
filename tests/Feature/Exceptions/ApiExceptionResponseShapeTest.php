<?php

declare(strict_types=1);

namespace Tests\Feature\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
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
