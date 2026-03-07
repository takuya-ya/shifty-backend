<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create($data);

            // デフォルトロール (Store Admin) を付与
            $role = Role::where('name', Role::ROLE_STORE_ADMIN)->first();
            if (!$role) {
                throw new \RuntimeException('デフォルトロールが存在しません');
            }

            $user->roles()->attach($role->id);
            
            event(new Registered($user));

            Auth::login($user);

            return $user;
        });
    }
}
