<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StaffProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween('now', '+30 days');

        return [
            'staff_id'    => StaffProfile::factory(),
            'start_at'    => $startAt->format('Y-m-d') . ' 09:00:00',
            'end_at'      => $startAt->format('Y-m-d') . ' 17:00:00',
            'shift_state' => 'draft',
            'position_id' => null,
            'memo'        => null,
        ];
    }
}
