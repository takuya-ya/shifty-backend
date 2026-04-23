<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PositionSeeder::class,
        ]);

        // 管理者ユーザー（重複実行に備えて firstOrCreate）
        if (! \App\Models\User::where('email', 'admin@example.com')->exists()) {
            User::factory()
                ->admin()
                ->withStaffProfile(['name' => '管理者'])
                ->create(['email' => 'admin@example.com']);
        }

        // スタッフユーザー × 5
        User::factory(5)
            ->staff()
            ->withStaffProfile()
            ->create();
    }
}
