<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Position;
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

        // PositionSeeder 実行後のIDを名前から解決する（IDの採番順に依存しないため）
        $hallId = Position::where('name', 'ホール')->value('id');
        $kitchenId = Position::where('name', 'キッチン')->value('id');

        // 管理者ユーザー（exists チェックで重複実行に対応）
        if (! User::where('email', 'admin@example.com')->exists()) {
            User::factory()
                ->admin()
                ->withStaffProfile(['name' => '管理者'])
                ->create(['email' => 'admin@example.com']);
        }

        // if 内に置くと既存ユーザーの場合スキップされるため、if の外で実行する
        User::where('email', 'admin@example.com')->first()
            ->staffProfile->positions()->sync([$hallId]);

        // スタッフユーザー × 5（重複実行に備えて exists チェック）
        foreach (range(1, 5) as $i) {
            $email = "staff{$i}@example.com";
            if (! User::where('email', $email)->exists()) {
                User::factory()
                    ->staff()
                    ->withStaffProfile(['name' => "スタッフ{$i}"])
                    ->create(['email' => $email]);
            }

            $positionIds = match (true) {
                $i <= 2 => [$hallId],
                $i === 3 => [$hallId, $kitchenId],
                default => [$kitchenId],
            };

            User::where('email', $email)->first()
                ->staffProfile->positions()->sync($positionIds);
        }

        $this->call([
            ShiftSeeder::class,
        ]);
    }
}
