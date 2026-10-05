<?php

declare(strict_types=1);

namespace App\Services\StaffProfile;

use App\Models\StaffProfile;
use App\Repositories\StaffProfileRepository;
use Illuminate\Support\Facades\DB;

class StaffProfileCommandService
{
    public function __construct(
        private readonly StaffProfileRepository $staffProfileRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): StaffProfile
    {
        return DB::transaction(fn () => $this->staffProfileRepository->create($data));
    }
}
