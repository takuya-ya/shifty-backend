<?php

declare(strict_types=1);

namespace App\Services\Shift;

use App\Models\Shift;
use App\Repositories\ShiftRepository;

class ShiftCommandService
{
    public function __construct(
        private readonly ShiftRepository $shiftRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Shift
    {
        $data['shift_state'] = 'draft';
        $data['version'] = 1;

        return $this->shiftRepository->create($data);
    }
}
