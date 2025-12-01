<?php

namespace App\Domain\Auth\Domain\Repository;

use App\Models\User;

/**
 * アカウントリポジトリ
 */
interface UserRepository
{
    /**
     * アカウント情報を取得する
     * @param int $id ホテルID
     * @return User
     */
    public function find(int $id): User;

    public function saveLastLogin(User $user): bool;
}
