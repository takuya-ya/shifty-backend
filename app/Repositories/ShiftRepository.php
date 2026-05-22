<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Shift;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ShiftRepository
{
    /**
     * 指定された日付期間（終日含む）のシフトを取得する
     *
     * @param string $from 引数は 'Y-m-d' 形式の日付
     * @param string $to   引数は 'Y-m-d' 形式の日付
     */
    public function findByPeriod(string $from, string $to): Collection
    {
        // 日付文字列をCarbonに変換し、それぞれの開始時刻と終了時刻を明示的に生成する
        $start = Carbon::parse($from)->startOfDay(); // 例: 2026-05-19 00:00:00
        $end = Carbon::parse($to)->endOfDay();       // 例: 2026-05-20 23:59:59

        return Shift::with(['staffProfile'])
            ->whereBetween('start_at', [$start, $end])
            ->get();
    }
}
