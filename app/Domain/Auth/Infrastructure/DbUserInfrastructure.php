<?php

namespace App\Domain\Auth\Infrastructure;

use App\Domain\Auth\Domain\Repository\UserRepository;
use App\Models\User;
use Carbon\Carbon;

/**
 * DBからのアカウント情報を扱うクラス
 */
class DbUserInfrastructure implements UserRepository
{
    /**
     * アカウント情報を取得する
     *
     * @param  int  $id  アカウントID
     * @return User
     */
    #[\Override] public function find(int $id): User
    {
        return User::where('id', $id)->firstOrFail();
    }

    /**
     * アカウント情報を保存する
     *
     * @param  User $user
     * @return bool
     */
    #[\Override] public function saveLastLogin(User $user): bool
    {
        $user->last_login_at = Carbon::now();
        return $user->save();
    }
}
