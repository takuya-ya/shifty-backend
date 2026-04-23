<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StaffProfile>
 */
class StaffProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake('ja_JP')->name(),
            'date_of_birth' => fake()->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
            'is_student' => fake()->boolean(30),
            'hourly_wage' => fake()->randomElement([1050, 1100, 1150, 1200, 1250]),
            'memo' => null,
            'max_consecutive_days' => null,
            'max_hours_per_week' => null,
        ];
    }
}
