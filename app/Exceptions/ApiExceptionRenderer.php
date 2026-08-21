<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Responses\ApiResponsePayload;
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
            // 本番環境では常に標準化されたメッセージを使用し、非本番ではカスタムメッセージを優先する
            if (app()->isProduction()) {
                $message = 'Validation failed';
            } else {
                $customMessage = $throwable->getMessage();
                $message = $customMessage !== '' ? $customMessage : 'Validation failed';
            }
            $errors = $throwable->errors();
        } elseif ($throwable instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated.';
        } elseif ($throwable instanceof AuthorizationException) {
            $status = 403;
            $standardText = Response::$statusTexts[$status] ?? 'Error';
            if (app()->isProduction()) {
                $message = $standardText;
            } else {
                $customMessage = $throwable->getMessage();
                $message = $customMessage !== '' ? $customMessage : $standardText;
            }
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
     * @param  array<string, array<int, string>>|null  $errors
     */
    private function errorResponse(int $status, string $message, ?array $errors = null): Response
    {
        return response()->json(
            new ApiResponsePayload(
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
