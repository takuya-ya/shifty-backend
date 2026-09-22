<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\StaffProfileResource;
use App\Services\StaffProfile\StaffProfileCommandService;
use App\Services\StaffProfile\StaffProfileQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffProfileController extends Controller
{
    public function __construct(
        private readonly StaffProfileQueryService $staffProfileQueryService,
        private readonly StaffProfileCommandService $staffProfileCommandService,
    ) {}

    public function index(): JsonResponse
    {
        $staffProfiles = $this->staffProfileQueryService->getStaffProfiles();

        return $this->success(data: StaffProfileResource::collection($staffProfiles));
    }

    public function store(Request $request): JsonResponse
    {
        $staffProfile = $this->staffProfileCommandService->create($request->all());

        return $this->success(data: new StaffProfileResource($staffProfile), status: 201);
    }
}
