<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Collection;

class ShiftRepository
{
    public function findByPeriod(string $from, string $to): Collection
    {
        return Shift::with(['staffProfile', 'position'])
            ->whereBetween('start_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])->get();
    }
}
