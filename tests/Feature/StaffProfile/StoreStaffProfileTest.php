<?php

declare(strict_types=1);

namespace Tests\Feature\StaffProfile;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class StoreStaffProfileTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/staffs';

    /** @var Collection<int, Position> */
    private Collection $positions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->positions = Position::factory()->count(2)->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => '山田 太郎',
            'position_ids' => $this->positions->pluck('id')->all(),
            'hourly_wage' => 1000,
        ], $overrides);
    }

    public function test_required_fields_only_creates_staff(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload();

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertCreated();

        $this->assertDatabaseHas('staff_profiles', [
            'name' => $payload['name'],
            'hourly_wage' => $payload['hourly_wage'],
        ]);
    }

    public function test_all_fields_creates_staff(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload([
            'date_of_birth' => '2000-04-01',
            'is_student' => false,
            'memo' => '土日のみ勤務可能',
        ]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertCreated();

        $this->assertDatabaseHas('staff_profiles', [
            'name' => $payload['name'],
            'hourly_wage' => $payload['hourly_wage'],
            'date_of_birth' => $payload['date_of_birth'],
            'is_student' => $payload['is_student'],
            'memo' => $payload['memo'],
        ]);
    }

    public function test_staff_positions_pivot_records_are_created(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload();

        $response = $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertCreated();

        $staffId = $response->json('data.id');

        foreach ($this->positions as $position) {
            $this->assertDatabaseHas('staff_positions', [
                'staff_id' => $staffId,
                'position_id' => $position->id,
            ]);
        }
        $this->assertDatabaseCount('staff_positions', $this->positions->count());
    }

    public function test_missing_name_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = Arr::except($this->validPayload(), ['name']);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('staff_profiles', 0);
    }

    public function test_empty_position_ids_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload(['position_ids' => []]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['position_ids']);

        $this->assertDatabaseCount('staff_profiles', 0);
    }

    public function test_nonexistent_position_id_returns_422(): void
    {
        $user = User::factory()->create();
        $nonexistentPositionId = Position::max('id') + 1;
        $payload = $this->validPayload(['position_ids' => [$nonexistentPositionId]]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['position_ids.0']);

        $this->assertDatabaseCount('staff_profiles', 0);
    }

    public function test_hourly_wage_exceeding_max_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload(['hourly_wage' => 5001]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['hourly_wage']);

        $this->assertDatabaseCount('staff_profiles', 0);
    }

    public function test_memo_exceeding_max_length_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload(['memo' => str_repeat('あ', 1001)]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['memo']);

        $this->assertDatabaseCount('staff_profiles', 0);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $payload = $this->validPayload();

        $this->postJson(self::ENDPOINT, $payload)
            ->assertUnauthorized();

        $this->assertDatabaseCount('staff_profiles', 0);
    }

    public function test_unverified_email_returns_403(): void
    {
        $user = User::factory()->unverified()->create();
        $payload = $this->validPayload();

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertForbidden();

        $this->assertDatabaseCount('staff_profiles', 0);
    }
}
