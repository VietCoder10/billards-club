<?php

namespace App\Domain\Auth\Domain\Entity;

use App\Models\User;

/**
 * 認証エンティティ
 * @package App\Domain\Auth\Domain\Entity
 */
class UserEntity
{
    private User $user;

    public function __construct(User $user)
    {
        $this->setUser($user);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
