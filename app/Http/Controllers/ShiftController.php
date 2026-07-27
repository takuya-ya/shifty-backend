<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Shift\IndexShiftRequest;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Services\Shift\ShiftCommandService;
use App\Services\Shift\ShiftQueryService;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    public function __construct(
        private readonly ShiftQueryService $shiftQueryService,
        private readonly ShiftCommandService $shiftCommandService,
    ) {}

    public function index(IndexShiftRequest $request): JsonResponse
    {
        $shifts = $this->shiftQueryService->getShiftsByPeriod($request->validated('from'), $request->validated('to'));

        return $this->success(data: ShiftResource::collection($shifts));
    }

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $shift = $this->shiftCommandService->create($request->validated());

        return $this->success(data: new ShiftResource($shift), status: 201);
    }
}
