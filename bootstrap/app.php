<?php

use App\Http\Middleware\AdminHandleInertiaRequests;
use App\Http\Middleware\UserHandleInertiaRequests;
use App\Http\Middleware\SiteAdmin;
use App\Http\Middleware\SiteUser;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AssignGuard;
use App\Http\Middleware\Admin;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware('siteAdmin')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
            Route::middleware('siteUser')
                ->group(base_path('routes/user.php'));

            // Route::middleware('web')
            //     ->group(base_path('routes/web.php'));

            Route::middleware('apiMiddleware')
                ->prefix('api/v1')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('siteAdmin', [
            StartSession::class,
            SiteAdmin::class,
            AdminHandleInertiaRequests::class,
            AddQueuedCookiesToResponse::class,
            ShareErrorsFromSession::class,
            SubstituteBindings::class,
        ]);

        $middleware->appendToGroup('siteUser', [
            StartSession::class,
            SiteUser::class,
            UserHandleInertiaRequests::class,
            AddQueuedCookiesToResponse::class,
            ShareErrorsFromSession::class,
            SubstituteBindings::class,
        ]);

        $middleware->appendToGroup('apiMiddleware', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $middleware->alias([
            'assign.guard' => AssignGuard::class,
            'admin' => Admin::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'jwt.verify' => \App\Http\Middleware\JwtVerify::class,
            'auth.jwt' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Exception $e, Request $request) {
            if ($request->is('api/*')) {
                $statusCode = 500;
                try {
                    $statusCode = $e->getStatusCode();
                } catch (\Throwable $th) {
                }
                return response()->json([
                    'message' => $e->getMessage(),
                    'status_code' => $statusCode
                ]);
            }
        });
    })->create();
