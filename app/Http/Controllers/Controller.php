<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponseTrait;

/**
 * APIコントローラの基底クラス。
 *
 * すべてのAPIコントローラはこのクラスを継承し、`success()` / `error()` メソッドで
 * 統一されたレスポンス形式を返却します。
 *
 * レスポンス形式（共通）:
 * {
 *   "status": "success" | "error",
 *   "data": mixed (成功時のみ、nullの場合は省略可),
 *   "message": string | null,
 *   "errors": {
 *     "fieldName": "エラーメッセージ" | ["エラー1", "エラー2"],
 *     ...
 *   } | null
 * }
 *
 * @see ApiResponseTrait success() / error() メソッドの実装
 */
abstract class Controller
{
    use ApiResponseTrait;
}
