<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PositionSeeder::class,
        ]);

        // 管理者ユーザー（exists チェックで重複実行に対応）
        if (! \App\Models\User::where('email', 'admin@example.com')->exists()) {
            User::factory()
                ->admin()
                ->withStaffProfile(['name' => '管理者'])
                ->create(['email' => 'admin@example.com']);
        }

        // スタッフユーザー × 5（重複実行に備えて exists チェック）
        foreach (range(1, 5) as $i) {
            $email = "staff{$i}@example.com";
            if (! User::where('email', $email)->exists()) {
                User::factory()
                    ->staff()
                    ->withStaffProfile(['name' => "スタッフ{$i}"])
                    ->create(['email' => $email]);
            }
        }
    }
}
