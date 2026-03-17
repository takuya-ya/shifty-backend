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
            $message = 'Validation failed';
            $errors = $this->normalizeValidationErrors($throwable->errors());
        } elseif ($throwable instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated.';
        } elseif ($throwable instanceof AuthorizationException) {
            $status = 403;
            $message = 'This action is unauthorized.';
        } elseif ($throwable instanceof HttpExceptionInterface) {
            $status = $throwable->getStatusCode();
            $standardText = Response::$statusTexts[$status] ?? 'Error';
            $message = app()->isProduction()
                ? $standardText
                : ($throwable->getMessage() !== '' ? $throwable->getMessage() : $standardText);
        }

        return $this->errorResponse(
            status: $status,
            message: $message,
            errors: $errors,
        );
    }

    /**
     * @param array<string, string|array<int, string>>|null $errors
     */
    private function errorResponse(int $status, string $message, ?array $errors = null): Response
    {
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

    /**
     * @param array<string, mixed> $errors
     * @return array<string, array<int, string>>
     */
    private function normalizeValidationErrors(array $errors): array
    {
        $normalized = [];

        foreach ($errors as $field => $messages) {
            if (is_array($messages)) {
                $normalized[(string) $field] = array_map(static fn(mixed $message): string => (string) $message, $messages);
                continue;
            }

            $normalized[(string) $field] = [(string) $messages];
        }

        return $normalized;
    }

    private function shouldRenderAsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}
