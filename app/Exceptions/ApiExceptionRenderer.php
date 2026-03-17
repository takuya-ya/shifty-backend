<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Responses\ApiResponsePayload;
use App\Http\Responses\ApiResponseStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionRenderer
{
    public function render(Throwable $throwable, Request $request): ?Response
    {
        if (! $this->shouldRenderAsJson($request)) {
            return null;
        }

        $status = 500;
        $message = 'Server Error';
        $errors = null;

        if ($throwable instanceof ValidationException) {
            $status = 422;
            $message = $throwable->getMessage() !== '' ? $throwable->getMessage() : 'The given data was invalid.';
            $errors = $throwable->errors();
        } elseif ($throwable instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated.';
        } elseif ($throwable instanceof AuthorizationException) {
            $status = 403;
            $message = $throwable->getMessage() !== '' ? $throwable->getMessage() : 'This action is unauthorized.';
        } elseif ($throwable instanceof HttpExceptionInterface) {
            $status = $throwable->getStatusCode();
            if ($throwable->getMessage() !== '') {
                $message = $throwable->getMessage();
            } elseif ($status === 404) {
                $message = 'Not found';
            } elseif (isset(Response::$statusTexts[$status])) {
                $message = Response::$statusTexts[$status];
            } else {
                $message = 'Error';
            }
        }

        return response()->json(
            new ApiResponsePayload(
                status: ApiResponseStatus::Error,
                data: null,
                message: $message,
                errors: $errors,
            ),
            $status,
        );
    }

    private function shouldRenderAsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}
