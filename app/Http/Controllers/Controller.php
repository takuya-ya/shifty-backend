<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponsePayload;
use App\Http\Responses\ApiResponseStatus;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * 成功レスポンスを返す。
     *
     * @param mixed $data レスポンスボディに含めるデータ
     * @param string|null $message クライアントへのメッセージ
     * @param int $status HTTPステータスコード（デフォルト: 200。詳細は docs を参照）
     */
    protected function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
    ): JsonResponse {
        $payload = new ApiResponsePayload(
            status: ApiResponseStatus::Success,
            data: $data,
            message: $message,
        );

        return response()->json($payload, $status);
    }

    /**
     * エラーレスポンスを返す。
     *
     * @param string $message クライアントへのエラーメッセージ
     * @param array<string, string|array<int, string>>|null $errors
     *     フィールド別エラー詳細。キー: フィールド名、値: エラーメッセージ（複数の場合は配列）。
     *     422時に必須、他のステータスでは通常 null。詳細は docs を参照。
     * @param int $status HTTPステータスコード（デフォルト: 400。詳細は docs を参照）
     */
    protected function error(
        string $message,
        ?array $errors = null,
        int $status = 400,
    ): JsonResponse {
        $payload = new ApiResponsePayload(
            status: ApiResponseStatus::Error,
            message: $message,
            errors: $errors,
        );

        return response()->json($payload, $status);
    }
}
