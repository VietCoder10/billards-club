<?php

namespace App\Domain\Auth\UseCase;

use App\Domain\Auth\Domain\Repository\UserRepository;
use App\Domain\Auth\Domain\Entity\AuthEntity;
use App\Domain\Auth\Exception\AuthenticationException;
use JWTAuth;

final class LogoutUseCase
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
     * @return AuthEntity
     * @throws AuthenticationException
     */
    public function __invoke(): AuthEntity
    {
        JWTAuth::parseToken()->invalidate(true);
        \Session::flush();
        return new AuthEntity(
            message: 'ユーザーが正常にログアウトしました',
        );
    }

}
