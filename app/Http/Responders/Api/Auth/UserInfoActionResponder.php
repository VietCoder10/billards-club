<?php

namespace App\Http\Responders\Api\Auth;

use App\Domain\Auth\Domain\Entity\UserEntity;
use App\Http\Resources\Api\Auth\UserInfoActionResource;

/**
 * 認証アクションのレスポンダー
 */
final class UserInfoActionResponder
{
    /**
     * レスポンスリソースの作成
     * @param UserEntity $userEntity
     * @return UserInfoActionResource
     */
    public function __invoke(UserEntity $userEntity): UserInfoActionResource
    {
        $resourceAry = $this->makeAuthForResource($userEntity);
        return new UserInfoActionResource($resourceAry);
    }

    /**
     * @param  UserEntity  $userEntity
     *
     * @return array
     */
    private function makeAuthForResource(UserEntity $userEntity)
    {
        return [
            'user_info' => $userEntity->getUser(),
        ];
    }
}
