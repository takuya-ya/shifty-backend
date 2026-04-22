<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use App\Models\StaffProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $_) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * StaffProfile を一緒に生成する state。
     *
     * @param  array<string, mixed>  $attributes
     */
    public function withStaffProfile(array $attributes = []): static
    {
        return $this->afterCreating(function (\App\Models\User $user) use ($attributes) {
            StaffProfile::factory()->create(array_merge(
                ['user_id' => $user->id],
                $attributes,
            ));
        });
    }

    /** Store Admin ロールを付与する state。 */
    public function admin(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $role = Role::firstOrCreate(['name' => Role::ROLE_STORE_ADMIN]);
            $user->roles()->syncWithoutDetaching([$role->id]);
        });
    }

    /** Staff ロールを付与する state。 */
    public function staff(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $role = Role::firstOrCreate(['name' => Role::ROLE_STAFF]);
            $user->roles()->syncWithoutDetaching([$role->id]);
        });
    }
}
