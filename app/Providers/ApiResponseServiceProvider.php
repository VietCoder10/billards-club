<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ApiResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        Response::macro('apiJsonResponse', function ($data) {
            return response()->json(
                $data
            );
        });

        // 正常終了
        Response::macro('apiSuccess', function ($summary, $data) {
            return response()->json(
                [
                    'success' => true,
                    'title' => $summary,
                    'summary' => $summary,
                    'data' => $data
                ]
            );
        });
        // 処理に失敗した時
        Response::macro('apiFailed', function ($summary, $data) {
            return response()->json(
                [
                    'success' => false,
                    'title' => $summary,
                    'summary' => $summary,
                    'data' => $data
                ]
            );
        });
        // エラー時
        Response::macro('apiError', function ($message, $error_code) {
            return response()->json(
                [
                    'message' => $message,
                    'status_code' => $error_code
                ],
                $error_code
            );
        });
        Response::macro('apiMissing', function () {
            return response()->json(
                [
                    'success' => false,
                    'title' => 'Not Found...',
                    'summary' => 'Not Found...',
                    'data' => 'Invalid ID'
                ]
            );
        });

        // 500
        Response::macro('apiInternalServerError', function ($summary, $detail = ['message' => 'Server Error']) {
            return response()->json(
                [
                    'status' => \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR,
                    'success' => false,
                    'title' => $summary,
                    'summary' => $summary,
                    'detail' => $detail,
                ],
                \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR
            );
        });

        // 422 Unprocessable Entity
        Response::macro('apiUnprocessableEntity', function ($errors) {
            return response()->json(
                [
                    'message' => 'エラーが発生しました。',
                    'errors' => $errors,
                    'status_code' => \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY
                ],
                \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY
            );
        });
    }
}
