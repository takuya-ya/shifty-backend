<?php

declare(strict_types=1);

namespace Tests\Feature\Shift;

use App\Http\Requests\Shift\StoreShiftRequest;
use App\Models\Position;
use App\Models\Shift;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftStoreTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/shifts';

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        $staffProfile = StaffProfile::factory()->create();

        return array_merge([
            'staff_id' => $staffProfile->id,
            'start_at' => '2026-08-01 09:00:00',
            'end_at' => '2026-08-01 17:00:00',
        ], $overrides);
    }

    public function test_valid_input_creates_shift_as_draft_with_version_1(): void
    {
        $user = User::factory()->create();
        $position = Position::factory()->create();
        $payload = $this->validPayload([
            'position_id' => $position->id,
            'memo' => 'オープン担当',
        ]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertCreated()
            ->assertJsonPath('data.staff_id', $payload['staff_id'])
            ->assertJsonPath('data.shift_state', 'draft');

        $this->assertDatabaseHas('shifts', [
            'staff_id' => $payload['staff_id'],
            'position_id' => $position->id,
            'shift_state' => 'draft',
            'version' => 1,
            'memo' => 'オープン担当',
        ]);
    }

    public function test_shift_can_be_created_without_position_and_memo(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload();

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertCreated();

        $this->assertDatabaseHas('shifts', [
            'staff_id' => $payload['staff_id'],
            'position_id' => null,
            'memo' => null,
        ]);
    }

    public function test_client_supplied_shift_state_and_version_are_ignored(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload([
            'shift_state' => 'confirmed',
            'version' => 99,
        ]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertCreated();

        $this->assertDatabaseHas('shifts', [
            'staff_id' => $payload['staff_id'],
            'shift_state' => 'draft',
            'version' => 1,
        ]);
    }

    public function test_end_at_equal_to_start_at_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload([
            'end_at' => '2026-08-01 09:00:00',
        ]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_at']);

        $this->assertDatabaseCount('shifts', 0);
    }

    public function test_duplicate_staff_id_and_start_at_returns_422(): void
    {
        $user = User::factory()->create();
        $staffProfile = StaffProfile::factory()->create();

        Shift::factory()->create([
            'staff_id' => $staffProfile->id,
            'start_at' => '2026-08-01 09:00:00',
            'end_at' => '2026-08-01 17:00:00',
        ]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, [
                'staff_id' => $staffProfile->id,
                'start_at' => '2026-08-01 09:00:00',
                'end_at' => '2026-08-01 18:00:00',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['staff_id']);

        $this->assertDatabaseCount('shifts', 1);
    }

    public function test_duplicate_with_soft_deleted_shift_returns_422(): void
    {
        // DB の UNIQUE (staff_id, start_at) はソフトデリート行も対象のため、
        // バリデーションを素通りさせると DB 例外で 500 になる。その防衛境界を検証する
        $user = User::factory()->create();
        $staffProfile = StaffProfile::factory()->create();

        $deletedShift = Shift::factory()->create([
            'staff_id' => $staffProfile->id,
            'start_at' => '2026-08-01 09:00:00',
            'end_at' => '2026-08-01 17:00:00',
        ]);
        $deletedShift->delete();

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, [
                'staff_id' => $staffProfile->id,
                'start_at' => '2026-08-01 09:00:00',
                'end_at' => '2026-08-01 18:00:00',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['staff_id']);
    }

    public function test_nonexistent_staff_id_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload();
        $nonexistentStaffId = $payload['staff_id'] + 1;

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, array_merge($payload, ['staff_id' => $nonexistentStaffId]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['staff_id']);

        $this->assertDatabaseCount('shifts', 0);
    }

    public function test_memo_exceeding_max_length_returns_422(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload([
            'memo' => str_repeat('あ', StoreShiftRequest::MEMO_MAX_LENGTH + 1),
        ]);

        $this->actingAs($user)
            ->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['memo']);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $payload = $this->validPayload();

        $this->postJson(self::ENDPOINT, $payload)
            ->assertUnauthorized();

        $this->assertDatabaseCount('shifts', 0);
    }
}
