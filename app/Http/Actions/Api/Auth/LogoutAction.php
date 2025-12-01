<?php

namespace App\Http\Actions\Api\Auth;

use App\Domain\Auth\UseCase\LogoutUseCase;
use App\Exceptions\Domain\NotFoundResourceException;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\Auth\LogoutActionResource;
use App\Http\Responders\Api\Auth\LogoutActionResponder;

class LogoutAction extends BaseController
{
    /**
     * @var LogoutUseCase 認証ユースケース
     */
    private LogoutUseCase $logoutUseCase;

    /**
     * @var LogoutActionResponder 認証レスポンダー
     */
    private LogoutActionResponder $responder;

    /**
     * constructor
     * @param LogoutUseCase $logoutUseCase
     * @param LogoutActionResponder $responder
     */
    public function __construct(
        LogoutUseCase $logoutUseCase,
        LogoutActionResponder $responder
    ) {
        $this->logoutUseCase = $logoutUseCase;
        $this->responder = $responder;
    }

    /**
     * アクション
     * @return LogoutActionResource
     * @throws NotFoundResourceException
     */
    /**
    *  @OA\Get(
    *      path="/api/v1/logout",
    *      tags={"Auth"},
    *      summary="logout",
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
    public function __invoke(): LogoutActionResource
    {
        // Execute useCase
        $authEntity = $this->logoutUseCase->__invoke();
        // Build response
        return $this->responder->__invoke(authEntity: $authEntity);
    }
}
