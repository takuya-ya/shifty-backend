<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\StaffProfileResource;
use App\Services\StaffProfile\StaffProfileQueryService;
use Illuminate\Http\JsonResponse;

class StaffProfileController extends Controller
{
    public function __construct(
        private readonly StaffProfileQueryService $staffProfileQueryService,
    ) {}

    public function index(): JsonResponse
    {
        $staffProfiles = $this->staffProfileQueryService->getStaff();

        return $this->success(data: StaffProfileResource::collection($staffProfiles));
    }
}
