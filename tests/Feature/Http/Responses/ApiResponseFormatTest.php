<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Responses;

use Tests\TestCase;

class ApiResponseFormatTest extends TestCase
{
    public function test_success_endpoint_returns_common_response_shape(): void
    {
        $response = $this->getJson('/api/v1/_debug/api-response/success');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'sample' => true,
                    'version' => 'v1',
                ],
                'message' => 'success sample',
                'errors' => null,
            ]);
    }

    public function test_error_endpoint_returns_common_response_shape(): void
    {
        $response = $this->getJson('/api/v1/_debug/api-response/error');

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'data' => null,
                'message' => 'validation failed',
                'errors' => [
                    'email' => ['The email field is required.'],
                ],
            ]);
    }
}
