<?php

declare(strict_types=1);

namespace Tests\Feature\Exceptions;

use Tests\TestCase;

class ApiExceptionNonJsonFallbackTest extends TestCase
{
    public function test_non_api_html_request_keeps_default_error_rendering(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'text/html',
        ])->get('/definitely-missing-page');

        $response->assertStatus(404);

        $contentType = (string) $response->headers->get('content-type');
        $this->assertStringNotContainsString('application/json', $contentType);
    }
}
