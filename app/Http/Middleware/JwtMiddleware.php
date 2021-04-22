<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelpers;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */


    public function handle($request, Closure $next)
    {
        $responseHelpers = new ResponseHelpers();
        
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json($responseHelpers->ResponseUserFormatter("Token Invalid", "Unauthorized", Response::HTTP_UNAUTHORIZED, null), Response::HTTP_UNAUTHORIZED);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json($responseHelpers->ResponseUserFormatter("Token is Expired", "Unauthorized", Response::HTTP_UNAUTHORIZED, null), Response::HTTP_UNAUTHORIZED);
            } else {
                return response()->json($responseHelpers->ResponseUserFormatter("Authorization Token not found", "Unauthorized", Response::HTTP_UNAUTHORIZED, null), Response::HTTP_UNAUTHORIZED);
            }
        }
        return $next($request);
    }
}
