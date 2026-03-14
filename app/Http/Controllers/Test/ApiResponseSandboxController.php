<?php

declare(strict_types=1);

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ApiResponseSandboxController extends Controller
{
    public function successSample(): JsonResponse
    {
        return $this->success(
            data: ['sample' => true, 'version' => 'v1'],
            message: 'success sample',
        );
    }

    public function errorSample(): JsonResponse
    {
        return $this->error(
            message: 'validation failed',
            errors: ['email' => ['The email field is required.']],
            status: 422,
        );
    }
}
