<?php

namespace App\Domain\Auth\UseCase;

use App\Domain\Auth\Domain\Repository\UserRepository;
use App\Domain\Auth\Domain\Entity\AuthEntity;
use App\Domain\Auth\Exception\AuthenticationException;
use App\Http\Requests\Api\Auth\LoginRequest;
use JWTAuth;

final class LoginUseCase
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
     * @param LoginRequest $request
     * @return AuthEntity
     * @throws AuthenticationException
     */
    public function __invoke(LoginRequest $request): AuthEntity
    {
        try {
            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                throw new AuthenticationException(message: "ユーザー名とパスワードが一致しません。");
            }
        } catch (JWTAuthException $e) {
            throw new AuthenticationException(previous: $e);
        }

        if (!$this->userRepository->saveLastLogin(JWTAuth::user())) {
            throw new AuthenticationException(message: "ユーザー名とパスワードが一致しません。");
        }

        // 認証情報をエンティティに変換する処理
        return $this->makeAuthEntity($token);
    }

    /**
     * 認証情報をエンティティに変換する
     * @param string $token アカウント情報
     * @return AuthEntity
     */
    private function makeAuthEntity(string $token): AuthEntity
    {
        return new AuthEntity(
            token: $token,
        );
    }

}
