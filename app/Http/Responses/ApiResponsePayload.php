<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * APIレスポンスの共通構造を定義するペイロード。
 *
 * @phpstan-type ApiResponseErrors array<string, string|array<int, string>>|null
 * @phpstan-type ApiResponseShape array{
 *     status: 'success'|'error',
 *     data: mixed,
 *     message: string|null,
 *     errors: ApiResponseErrors
 * }
 */
final readonly class ApiResponsePayload implements Arrayable, JsonSerializable
{
  /**
   * @param array<string, string|array<int, string>>|null $errors
   */
  public function __construct(
    public ApiResponseStatus $status,
    public mixed $data = null,
    public ?string $message = null,
    public ?array $errors = null,
  ) {}

  /**
   * @return array{
   *     status: 'success'|'error',
   *     data: mixed,
   *     message: string|null,
   *     errors: array<string, string|array<int, string>>|null
   * }
   */
  public function toArray(): array
  {
    return [
      'status' => $this->status->value,
      'data' => $this->data,
      'message' => $this->message,
      'errors' => $this->errors,
    ];
  }

  /**
   * @return array{
   *     status: 'success'|'error',
   *     data: mixed,
   *     message: string|null,
   *     errors: array<string, string|array<int, string>>|null
   * }
   */
  public function jsonSerialize(): array
  {
    return $this->toArray();
  }
}
