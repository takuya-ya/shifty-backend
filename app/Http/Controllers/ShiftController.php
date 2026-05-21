<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Shift\ShiftIndexRequest;
use App\Http\Resources\ShiftResource;
use App\Http\Responses\ApiResponseTrait;
use App\Services\Shift\ShiftQueryService;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ShiftQueryService $shiftQueryService,
    ) {}

    public function index(ShiftIndexRequest $request): JsonResponse
    {
        $shifts = $this->shiftQueryService->getShiftsByPeriod($request->validated('from'), $request->validated('to'));

        return $this->success(data: ShiftResource::collection($shifts));
    }
}
