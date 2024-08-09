<?php

namespace App\Http\Middleware;

use App\Exceptions\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                throw new ErrorResponse(
                    message: 'Token is invalid',
                    code: 401,
                );
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                throw new ErrorResponse(
                    message: 'Token is expired',
                    code: 401
                );
            } else {
                throw new ErrorResponse(
                    message: 'Authorization Token not found',
                    code: 401,
                );
            }
        }

        return $next($request);
    }
}
