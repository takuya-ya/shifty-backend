<?php

declare(strict_types=1);

namespace App\Services\StaffProfile;

use App\Repositories\StaffProfileRepository;
use Illuminate\Database\Eloquent\Collection;

class StaffProfileQueryService
{
    public function __construct(
        private readonly StaffProfileRepository $staffProfileRepository,
    ) {}

    public function getStaffProfiles(): Collection
    {
        return $this->staffProfileRepository->findAll();
    }
}
