<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\StaffProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class StaffProfileRepository
{
    public function findAll(): Collection
    {
        return StaffProfile::with('positions')->orderBy('id')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): StaffProfile
    {
        $positionIds = $data['position_ids'];

        $staffProfile = StaffProfile::create(Arr::except($data, ['position_ids']));
        $staffProfile->positions()->attach($positionIds);
        $staffProfile->load('positions');

        return $staffProfile;
    }
}
