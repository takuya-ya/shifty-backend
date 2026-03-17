<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use Tests\TestCase;

class LocalOnlyTest extends TestCase
{
    public function test_returns_200_in_local(): void
    {
        $this->app['env'] = 'local';

        $this->getJson('/api/v1/_debug/api-response/success')->assertStatus(200);
    }

    public function test_returns_403_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->getJson('/api/v1/_debug/api-response/success')
            ->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'data',
                'message',
                'errors',
            ])
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Forbidden')
            ->assertJsonPath('errors', null);
    }
}
