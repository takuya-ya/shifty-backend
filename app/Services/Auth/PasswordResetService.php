<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    /**
     * パスワードリセットリンクを送信する
     *
     * @param array $data
     * @return string
     * @throws ValidationException
     */
    public function sendResetLink(array $data): string
    {
        $status = Password::sendResetLink($data);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * パスワードをリセットする
     *
     * @param array $data
     * @return string
     * @throws ValidationException
     */
    public function reset(array $data): string
    {
        $status = Password::reset(
            $data,
            function ($user) use ($data) {
                $this->userRepository->updatePassword($user, $data['password']);

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }
}
