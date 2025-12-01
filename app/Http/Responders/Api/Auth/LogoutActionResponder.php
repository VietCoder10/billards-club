<?php

namespace App\Http\Responders\Api\Auth;

use App\Domain\Auth\Domain\Entity\AuthEntity;
use App\Http\Resources\Api\Auth\LogoutActionResource;

/**
 * 認証アクションのレスポンダー
 */
final class LogoutActionResponder
{
    /**
     * レスポンスリソースの作成
     * @param AuthEntity $authEntity
     * @return LogoutActionResource
     */
    public function __invoke(AuthEntity $authEntity): LogoutActionResource
    {
        $resourceAry = $this->makeAuthForResource($authEntity);
        return new LogoutActionResource($resourceAry);
    }

    /**
     * @param  AuthEntity  $authEntity
     *
     * @return array
     */
    private function makeAuthForResource(AuthEntity $authEntity)
    {
        return [
            'message' => $authEntity->getMessage(),
        ];
    }
}
