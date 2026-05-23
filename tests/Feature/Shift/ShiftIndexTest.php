<?php

declare(strict_types=1);

namespace Tests\Feature\Shift;

use App\Models\Position;
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

    public function test_shift_fields_are_returned_with_correct_values(): void
    {
        $user = User::factory()->create();
        $staffProfile = StaffProfile::factory()->create();
        $position = Position::factory()->create();

        Shift::factory()->create([
            'staff_id'    => $staffProfile->id,
            'start_at'    => '2026-05-05 09:00:00',
            'end_at'      => '2026-05-05 17:00:00',
            'shift_state' => 'confirmed',
            'position_id' => $position->id,
            'memo'        => 'テスト用メモ',
        ]);

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertOk()
            ->assertJsonPath('data.0.shift_state', 'confirmed')
            ->assertJsonPath('data.0.position_id', $position->id)
            ->assertJsonPath('data.0.memo', 'テスト用メモ');
    }

    public function test_start_at_and_end_at_are_returned_in_iso8601_format(): void
    {
        $user = User::factory()->create();
        $staffProfile = StaffProfile::factory()->create();

        Shift::factory()->create([
            'staff_id' => $staffProfile->id,
            'start_at' => '2026-05-05 09:00:00',
            'end_at'   => '2026-05-05 17:00:00',
        ]);

        $iso8601Pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/';

        $response = $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertOk();

        $this->assertMatchesRegularExpression($iso8601Pattern, $response->json('data.0.start_at'));
        $this->assertMatchesRegularExpression($iso8601Pattern, $response->json('data.0.end_at'));
    }

    public function test_all_shifts_within_period_are_returned(): void
    {
        $user = User::factory()->create();
        $staffProfile = StaffProfile::factory()->create();

        Shift::factory()->createMany([
            ['staff_id' => $staffProfile->id, 'start_at' => '2026-05-01 09:00:00', 'end_at' => '2026-05-01 17:00:00'],
            ['staff_id' => $staffProfile->id, 'start_at' => '2026-05-05 09:00:00', 'end_at' => '2026-05-05 17:00:00'],
            ['staff_id' => $staffProfile->id, 'start_at' => '2026-05-15 09:00:00', 'end_at' => '2026-05-15 17:00:00'],
        ]);

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_shifts_on_boundary_dates_are_included(): void
    {
        $user = User::factory()->create();
        $staffProfile = StaffProfile::factory()->create();

        Shift::factory()->createMany([
            ['staff_id' => $staffProfile->id, 'start_at' => '2026-05-01 00:00:00', 'end_at' => '2026-05-01 08:00:00'],
            ['staff_id' => $staffProfile->id, 'start_at' => '2026-05-15 23:59:59', 'end_at' => '2026-05-16 07:00:00'],
        ]);

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertOk()
            ->assertJsonCount(2, 'data');
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

    public function test_each_shift_returns_its_own_staff_profile(): void
    {
        $user = User::factory()->create();
        $staffProfile1 = StaffProfile::factory()->create();
        $staffProfile2 = StaffProfile::factory()->create();

        Shift::factory()->create([
            'staff_id' => $staffProfile1->id,
            'start_at' => '2026-05-05 09:00:00',
            'end_at'   => '2026-05-05 17:00:00',
        ]);
        Shift::factory()->create([
            'staff_id' => $staffProfile2->id,
            'start_at' => '2026-05-06 09:00:00',
            'end_at'   => '2026-05-06 17:00:00',
        ]);

        $this->actingAs($user)
            ->getJson(self::ENDPOINT . '?from=2026-05-01&to=2026-05-15')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.staff_id', $staffProfile1->id)
            ->assertJsonPath('data.0.staff_profile.name', $staffProfile1->name)
            ->assertJsonPath('data.1.staff_id', $staffProfile2->id)
            ->assertJsonPath('data.1.staff_profile.name', $staffProfile2->name);
    }
}
