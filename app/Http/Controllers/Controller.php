<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponseTrait;

/**
 * @see ApiResponseTrait
 */
abstract class Controller
{
    use ApiResponseTrait;
}
