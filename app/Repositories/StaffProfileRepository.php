<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\StaffProfile;
use Illuminate\Database\Eloquent\Collection;

class StaffProfileRepository
{
    public function findAll(): Collection
    {
        return StaffProfile::with('positions')->orderBy('id')->get();
    }
}
