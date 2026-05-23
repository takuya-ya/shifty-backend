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
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        return Shift::with(['staffProfile'])
            ->whereBetween('start_at', [$start, $end])
            ->orderBy('start_at')
            ->get();
    }
}
