<?php

namespace App\Domain\Auth\UseCase;

use App\Domain\Auth\Domain\Repository\UserRepository;
use App\Domain\Auth\Domain\Entity\UserEntity;
use App\Domain\Auth\Exception\AuthenticationException;
use JWTAuth;

final class UserInfoUseCase
{
    /**
     * @var UserRepository アカウントリポジトリ
     */
    private UserRepository $userRepository;

    /**
     * constructor
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * ユースケースの実施
     * @return UserEntity
     * @throws AuthenticationException
     */
    public function __invoke(): UserEntity
    {
        return new UserEntity(
            user: JWTAuth::user()
        );
    }

}
