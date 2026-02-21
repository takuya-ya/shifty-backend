<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegistrationService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    /**
     * ユーザー登録処理
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $user = $this->userRepository->create($data);

        event(new Registered($user));

        Auth::login($user);

        return $user;
    }
}
