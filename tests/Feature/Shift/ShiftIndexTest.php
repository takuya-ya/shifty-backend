<?php

declare(strict_types=1);

namespace Tests\Feature\Shift;

use App\Models\Shift;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftIndexTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/shifts';

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertUnauthorized();
    }

    public function test_unverified_email_returns_403(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertForbidden();
    }

    public function test_missing_from_param_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?to=2026-05-15')
            ->assertUnprocessable();
    }

    public function test_missing_to_param_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01')
            ->assertUnprocessable();
    }

    public function test_invalid_date_format_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026/05/01&to=2026/05/15')
            ->assertUnprocessable();
    }

    public function test_to_before_from_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-15&to=2026-05-01')
            ->assertUnprocessable();
    }

    public function test_valid_request_returns_200_with_empty_collection(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'data'   => [],
            ]);
    }

    public function test_returns_shifts_within_period_with_staff_profile(): void
    {
        $user = User::factory()->create();
        $staffProfile = StaffProfile::factory()->create();

        Shift::factory()->create([
            'staff_id' => $staffProfile->id,
            'start_at' => '2026-05-05 09:00:00',
            'end_at'   => '2026-05-05 17:00:00',
        ]);

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.staff_id', $staffProfile->id)
            ->assertJsonPath('data.0.staff_profile.name', $staffProfile->name);
    }

    public function test_shifts_outside_period_are_not_returned(): void
    {
        $user = User::factory()->create();
        $staffProfile = StaffProfile::factory()->create();

        Shift::factory()->create([
            'staff_id' => $staffProfile->id,
            'start_at' => '2026-05-20 09:00:00',
            'end_at'   => '2026-05-20 17:00:00',
        ]);

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
