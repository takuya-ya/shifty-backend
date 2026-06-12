<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Shift;
use App\Models\StaffProfile;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $hall     = Position::where('name', 'ホール')->first();
        $kitchen  = Position::where('name', 'キッチン')->first();
        $staffIds = StaffProfile::pluck('id')->take(5);

        if ($staffIds->isEmpty() || ! $hall || ! $kitchen) {
            return;
        }

        $positions   = [$hall->id, $kitchen->id];
        $states      = ['draft', 'confirmed'];
        $baseDate    = now()->startOfMonth();

        foreach ($staffIds as $index => $staffId) {
            // 各スタッフに当月1〜7日の日程でシフトを生成
            foreach (range(1, 7) as $day) {
                $date        = $baseDate->copy()->addDays($day - 1);
                $positionId  = $positions[$index % count($positions)];
                $state       = $states[$day % count($states)];

                Shift::firstOrCreate(
                    [
                        'staff_id' => $staffId,
                        'start_at' => $date->format('Y-m-d') . ' 09:00:00',
                    ],
                    [
                        'end_at'      => $date->format('Y-m-d') . ' 17:00:00',
                        'shift_state' => $state,
                        'position_id' => $positionId,
                        'memo'        => null,
                    ]
                );
            }
        }
    }
}
