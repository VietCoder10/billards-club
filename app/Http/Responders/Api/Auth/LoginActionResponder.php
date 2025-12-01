<?php

namespace App\Http\Responders\Api\Auth;

use App\Domain\Auth\Domain\Entity\AuthEntity;
use App\Http\Resources\Api\Auth\LoginActionResource;

/**
 * 認証アクションのレスポンダー
 */
final class LoginActionResponder
{
    /**
     * レスポンスリソースの作成
     * @param AuthEntity $authEntity
     * @return LoginActionResource
     */
    public function __invoke(AuthEntity $authEntity): LoginActionResource
    {
        $resourceAry = $this->makeAuthForResource($authEntity);
        return new LoginActionResource($resourceAry);
    }

    /**
     * @param  AuthEntity  $authEntity
     *
     * @return array
     */
    private function makeAuthForResource(AuthEntity $authEntity)
    {
        return [
            'token' => $authEntity->getToken(),
        ];
    }
}
