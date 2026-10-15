<?php

declare(strict_types=1);

namespace Tests\Feature\StaffProfile;

use App\Models\Position;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class UpdateStaffProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @var Collection<int, Position> */
    private Collection $positions;

    private StaffProfile $staffProfile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->positions = Position::factory()->count(2)->create();
        $this->staffProfile = StaffProfile::factory()->create();
        $this->staffProfile->positions()->attach($this->positions->pluck('id'));
    }

    private function endpoint(): string
    {
        return '/api/v1/staffs/'.$this->staffProfile->id;
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => '鈴木 花子',
            'position_ids' => $this->positions->pluck('id')->all(),
            'hourly_wage' => 1200,
        ], $overrides);
    }

    public function test_required_fields_only_updates_staff(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload();

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertOk();

        $this->assertDatabaseHas('staff_profiles', [
            'id' => $this->staffProfile->id,
            'name' => $payload['name'],
            'hourly_wage' => $payload['hourly_wage'],
        ]);
    }

    public function test_all_fields_updates_staff(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload([
            'date_of_birth' => '2000-04-01',
            'is_student' => false,
            'memo' => '土日のみ勤務可能',
        ]);

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertOk();

        $this->assertDatabaseHas('staff_profiles', [
            'id' => $this->staffProfile->id,
            'name' => $payload['name'],
            'hourly_wage' => $payload['hourly_wage'],
            'date_of_birth' => $payload['date_of_birth'],
            'is_student' => $payload['is_student'],
            'memo' => $payload['memo'],
        ]);
    }

    public function test_position_ids_are_synced(): void
    {
        $user = User::factory()->create();
        $keptPosition = $this->positions->first();
        $removedPosition = $this->positions->last();
        $addedPosition = Position::factory()->create();

        $payload = $this->validPayload([
            'position_ids' => [$keptPosition->id, $addedPosition->id],
        ]);

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertOk();

        $this->assertDatabaseHas('staff_positions', [
            'staff_id' => $this->staffProfile->id,
            'position_id' => $keptPosition->id,
        ]);
        $this->assertDatabaseHas('staff_positions', [
            'staff_id' => $this->staffProfile->id,
            'position_id' => $addedPosition->id,
        ]);
        $this->assertDatabaseMissing('staff_positions', [
            'staff_id' => $this->staffProfile->id,
            'position_id' => $removedPosition->id,
        ]);
    }

    public function test_missing_name_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = Arr::except($this->validPayload(), ['name']);

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_empty_position_ids_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload(['position_ids' => []]);

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['position_ids']);
    }

    public function test_nonexistent_position_id_returns_422(): void
    {
        $user = User::factory()->create();
        $nonexistentPositionId = Position::max('id') + 1;
        $payload = $this->validPayload(['position_ids' => [$nonexistentPositionId]]);

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['position_ids.0']);
    }

    public function test_duplicate_position_ids_returns_422(): void
    {
        $user = User::factory()->create();
        $positionId = $this->positions->first()->id;
        $payload = $this->validPayload(['position_ids' => [$positionId, $positionId]]);

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['position_ids.0', 'position_ids.1']);
    }

    public function test_hourly_wage_exceeding_max_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload(['hourly_wage' => 5001]);

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['hourly_wage']);
    }

    public function test_memo_exceeding_max_length_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload(['memo' => str_repeat('あ', 1001)]);

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['memo']);
    }

    public function test_nonexistent_staff_id_returns_404(): void
    {
        $user = User::factory()->create();
        $nonexistentStaffId = StaffProfile::max('id') + 1;
        $payload = $this->validPayload();

        $this->actingAs($user)
            ->putJson('/api/v1/staffs/'.$nonexistentStaffId, $payload)
            ->assertNotFound();
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $payload = $this->validPayload();

        $this->putJson($this->endpoint(), $payload)
            ->assertUnauthorized();
    }

    public function test_unverified_email_returns_403(): void
    {
        $user = User::factory()->unverified()->create();
        $payload = $this->validPayload();

        $this->actingAs($user)
            ->putJson($this->endpoint(), $payload)
            ->assertForbidden();
    }
}
