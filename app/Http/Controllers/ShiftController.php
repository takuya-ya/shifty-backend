<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponseTrait;
use App\Services\Shift\ShiftQueryService;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ShiftQueryService $shiftQueryService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(data: null);
    }
}
