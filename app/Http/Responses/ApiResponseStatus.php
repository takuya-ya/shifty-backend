<?php

declare(strict_types=1);

namespace App\Http\Responses;

enum ApiResponseStatus: string
{
    case Success = 'success';
    case Error = 'error';
}
