<?php

namespace App\Http\Actions\Api\Auth;

use App\Domain\Auth\UseCase\LoginUseCase;
use App\Exceptions\Domain\NotFoundResourceException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\Api\Auth\LoginActionResource;
use App\Http\Responders\Api\Auth\LoginActionResponder;

/** @OA\Info(title="Laravel App API", version="1.0.0")
 * @OA\SecurityScheme(securityScheme="bearerAuth", type="http", scheme="bearer", bearerFormat="JWT")*/
class LoginAction extends BaseController
{
    /**
     * @var LoginUseCase 認証ユースケース
     */
    private LoginUseCase $loginUseCase;

    /**
     * @var LoginActionResponder 認証レスポンダー
     */
    private LoginActionResponder $responder;

    /**
     * constructor
     * @param LoginUseCase $loginUseCase
     * @param LoginActionResponder $responder
     */
    public function __construct(
        LoginUseCase $loginUseCase,
        LoginActionResponder $responder
    ) {
        $this->loginUseCase = $loginUseCase;
        $this->responder = $responder;
    }

    /**
     * アクション
     * @param LoginRequest $request
     * @return LoginActionResource
     * @throws NotFoundResourceException
     */

    /**
    *  @OA\Post(
    *      path="/api/v1/login",
    *      tags={"Auth"},
    *      summary="Login",
    *      security={{"bearerAuth":{}}},
    * @OA\RequestBody(
    *      @OA\MediaType(
    *          mediaType="multipart/form-data",
    *          @OA\Schema(
    *              @OA\Property(
    *                  property="email",
    *                  type="string"
    *              ),
    *              @OA\Property(
    *                  property="password",
    *                  type="string"
    *              ),
    *              example={"email": "admin@gmail.com", "nonce": "Laravel@2024"}
    *          )
    *      )
    *  ),
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
    public function __invoke(LoginRequest $request): LoginActionResource
    {
        // Execute useCase
        $authEntity = $this->loginUseCase->__invoke($request);
        // Build response
        return $this->responder->__invoke(authEntity: $authEntity);
    }
}
