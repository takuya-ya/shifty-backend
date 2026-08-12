<?php

declare(strict_types=1);

namespace Tests\Feature\Shift;

use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateShiftTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->shift = Shift::factory()->create([
            'start_at' => '2026-08-01 09:00:00',
            'end_at' => '2026-08-01 17:00:00',
            'shift_state' => 'draft',
            'version' => 1,
            'memo' => null,
        ]);
    }

    private function endpoint(int $shiftId): string
    {
        return "/api/v1/shifts/{$shiftId}";
    }

    public function test_valid_update_changes_field_and_preserves_others_with_version_increment(): void
    {
        $position = Position::factory()->create();

        $this->actingAs($this->user)
            ->patchJson($this->endpoint($this->shift->id), [
                'memo' => '早番担当',
                'position_id' => $position->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.memo', '早番担当');

        $this->assertDatabaseHas('shifts', [
            'id' => $this->shift->id,
            'memo' => '早番担当',
            'position_id' => $position->id,
            'start_at' => '2026-08-01 09:00:00',
            'end_at' => '2026-08-01 17:00:00',
            'shift_state' => 'draft',
            'version' => 2,
        ]);
    }

    public function test_updating_start_at_to_same_value_does_not_trigger_duplicate_error(): void
    {
        $this->actingAs($this->user)
            ->patchJson($this->endpoint($this->shift->id), [
                'start_at' => '2026-08-01 09:00:00',
            ])
            ->assertOk();
    }

    public function test_different_staff_with_same_start_at_does_not_trigger_duplicate_error(): void
    {
        $otherShift = Shift::factory()->create([
            'start_at' => '2026-08-01 10:00:00',
            'end_at' => '2026-08-01 18:00:00',
        ]);

        $this->actingAs($this->user)
            ->patchJson($this->endpoint($otherShift->id), [
                'start_at' => '2026-08-01 09:00:00',
            ])
            ->assertOk();
    }

    public function test_nonexistent_shift_returns_404(): void
    {
        $this->actingAs($this->user)
            ->patchJson($this->endpoint(99999), [
                'memo' => 'not found',
            ])
            ->assertNotFound();
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->patchJson($this->endpoint($this->shift->id), [
            'memo' => 'unauthorized',
        ])
            ->assertUnauthorized();
    }
}
