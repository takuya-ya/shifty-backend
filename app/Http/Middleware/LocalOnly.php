<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponsePayload;
use App\Http\Responses\ApiResponseStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalOnly
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local', 'testing')) {
            return response()->json(
                new ApiResponsePayload(
                    status: ApiResponseStatus::Error,
                    data: null,
                    message: Response::$statusTexts[403] ?? 'Forbidden',
                    errors: null,
                ),
                403,
            );
        }

        return $next($request);
    }
}
