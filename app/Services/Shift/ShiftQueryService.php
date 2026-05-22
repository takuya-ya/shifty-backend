<?php

declare(strict_types=1);

namespace App\Services\Shift;

use App\Repositories\ShiftRepository;
use Illuminate\Database\Eloquent\Collection;

class ShiftQueryService
{
    public function __construct(
        private readonly ShiftRepository $shiftRepository,
    ) {}

    public function getShiftsByPeriod(string $from, string $to): Collection
    {
        return $this->shiftRepository->findByPeriod($from, $to);
    }
}
