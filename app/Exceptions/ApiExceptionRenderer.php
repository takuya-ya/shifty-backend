<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ApiExceptionRenderer
{
  public function render(Throwable $throwable, Request $request): ?Response
  {
    return null;
  }
}
