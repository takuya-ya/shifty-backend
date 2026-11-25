<?php

declare(strict_types=1);

namespace Tests\Feature\StaffProfile;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyStaffProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private StaffProfile $staffProfile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->staffProfile = StaffProfile::factory()->create();
    }

    private function endpoint(int $staffProfileId): string
    {
        return "/api/v1/staffs/{$staffProfileId}";
    }

    public function test_valid_delete_returns_204_and_soft_deletes_staff_profile(): void
    {
        $this->actingAs($this->user)
            ->deleteJson($this->endpoint($this->staffProfile->id))
            ->assertNoContent();

        $this->assertSoftDeleted('staff_profiles', ['id' => $this->staffProfile->id]);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->deleteJson($this->endpoint($this->staffProfile->id))
            ->assertUnauthorized();
    }

    public function test_unverified_email_returns_403(): void
    {
        $unverifiedUser = User::factory()->unverified()->create();

        $this->actingAs($unverifiedUser)
            ->deleteJson($this->endpoint($this->staffProfile->id))
            ->assertForbidden();
    }

    public function test_nonexistent_staff_profile_returns_404(): void
    {
        $this->actingAs($this->user)
            ->deleteJson($this->endpoint(99999))
            ->assertNotFound();
    }

    public function test_soft_deleted_staff_profile_returns_404(): void
    {
        $this->staffProfile->delete();

        $this->actingAs($this->user)
            ->deleteJson($this->endpoint($this->staffProfile->id))
            ->assertNotFound();
    }
}
