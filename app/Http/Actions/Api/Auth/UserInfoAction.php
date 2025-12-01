<?php

namespace App\Http\Actions\Api\Auth;

use App\Domain\Auth\UseCase\UserInfoUseCase;
use App\Exceptions\Domain\NotFoundResourceException;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\Auth\UserInfoActionResource;
use App\Http\Responders\Api\Auth\UserInfoActionResponder;

class UserInfoAction extends BaseController
{
    /**
     * @var UserInfoUseCase 認証ユースケース
     */
    private UserInfoUseCase $userInfoUseCase;

    /**
     * @var UserInfoActionResponder 認証レスポンダー
     */
    private UserInfoActionResponder $responder;

    /**
     * constructor
     * @param UserInfoUseCase $userInfoUseCase
     * @param UserInfoActionResponder $responder
     */
    public function __construct(
        UserInfoUseCase $userInfoUseCase,
        UserInfoActionResponder $responder
    ) {
        $this->userInfoUseCase = $userInfoUseCase;
        $this->responder = $responder;
    }

    /**
     * アクション
     * @return UserInfoActionResource
     * @throws NotFoundResourceException
     */
    /**
     *  @OA\GET(
     *      path="/api/v1/user-info",
     *      tags={"Auth"},
     *      summary="get current user info",
     *      security={{"bearerAuth":{}}},
     *  @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *      )
     *  ),
     *  @OA\Response(
     *      response=400,
     *      description="Invalid request"
     *  ),
     * @OA\Response(
     *      response=401,
     *      description="Unauthorized"
     *  ),
     *  @OA\Response(
     *      response=500,
     *      description="Internal Server Error"
     *  ),
     *  )
     **/
    public function __invoke(): UserInfoActionResource
    {
        // Execute useCase
        $userInfo = $this->userInfoUseCase->__invoke();
        // Build response
        return $this->responder->__invoke(userEntity: $userInfo);
    }
}
