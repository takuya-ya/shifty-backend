<?php

declare(strict_types=1);

namespace Tests\Feature\Shift;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyShiftTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->shift = Shift::factory()->create([
            'shift_state' => 'draft',
        ]);
    }

    private function endpoint(int $shiftId): string
    {
        return "/api/v1/shifts/{$shiftId}";
    }

    public function test_valid_delete_returns_204_and_soft_deletes_shift(): void
    {
        $this->actingAs($this->user)
            ->deleteJson($this->endpoint($this->shift->id))
            ->assertNoContent();

        $this->assertDatabaseMissing('shifts', [
            'id' => $this->shift->id,
            'deleted_at' => null,
        ])->assertDatabaseHas('shifts', [
            'id' => $this->shift->id,
        ]);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->deleteJson($this->endpoint($this->shift->id))
            ->assertUnauthorized();
    }

    public function test_nonexistent_shift_returns_404(): void
    {
        $this->actingAs($this->user)
            ->deleteJson($this->endpoint(99999))
            ->assertNotFound();
    }

    public function test_soft_deleted_shift_returns_404(): void
    {
        $this->shift->delete();

        $this->actingAs($this->user)
            ->deleteJson($this->endpoint($this->shift->id))
            ->assertNotFound();
    }
}
