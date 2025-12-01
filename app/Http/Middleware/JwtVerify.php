<?php

namespace App\Http\Middleware;

use App\Enums\StatusCode;
use Closure;
use JWTAuth;

class JwtVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (! $user) {
                return response()->json(['status_code' => StatusCode::UNAUTHORIZED, 'message' => 'Unauthorized'], StatusCode::OK);
            }
        } catch (\Throwable $e) {
            return response()->json(['status_code' => StatusCode::UNAUTHORIZED, 'message' => 'Unauthorized'], StatusCode::OK);
        }

        return $next($request);
    }
}
