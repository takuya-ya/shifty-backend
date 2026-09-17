<?php

declare(strict_types=1);

namespace Tests\Feature\StaffProfile;

use App\Models\Position;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexStaffProfileTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/staffs';

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson(self::ENDPOINT)
            ->assertUnauthorized();
    }

    public function test_unverified_email_returns_403(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->getJson(self::ENDPOINT)
            ->assertForbidden();
    }

    public function test_returns_staff_list_with_positions(): void
    {
        $user = User::factory()->create();
        $staffProfile = StaffProfile::factory()->create();
        $positions = Position::factory()->count(2)->create();
        $staffProfile->positions()->attach($positions->pluck('id'));

        $response = $this->actingAs($user)
            ->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $data = $response->json('data.0');
        $this->assertSame($staffProfile->id, $data['id']);
        $this->assertSame($staffProfile->name, $data['name']);
        $this->assertArrayHasKey('hourly_wage', $data);
        $this->assertArrayHasKey('is_student', $data);
        $this->assertArrayHasKey('memo', $data);
        $this->assertCount(2, $data['positions']);
        $this->assertSame($positions[0]->id, $data['positions'][0]['id']);
        $this->assertSame($positions[0]->name, $data['positions'][0]['name']);
    }
}
