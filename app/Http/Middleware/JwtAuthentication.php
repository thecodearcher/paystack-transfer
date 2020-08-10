<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\ApiResponseTrait;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtAuthentication
{

    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('Authorization')) {
            try {
                $jwtToken =  JWTAuth::parseToken();
                $jwtToken->authenticate();

                return $next($request);
            } catch (TokenExpiredException $e) {
                return $this->custom(null, 'Token expired', false, 401);
            } catch (TokenInvalidException $e) {
                return $this->custom(null, 'Token invalid', false, 401);
            } catch (JWTException $e) {
                return $this->custom(null, 'Token absent', false, 401);
            }
        }
        return $this->badRequest('no "Authorization" header found');

    }

}
