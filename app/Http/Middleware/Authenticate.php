<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Helper\GenerateJWT;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($request->bearerToken('token')) {

            $jwt = $request->bearerToken('token');

            $decoded = (new GenerateJWT)->decodejwt($jwt);

            if (gettype($decoded) === "array") {
                $user = User::where([['email', $decoded['sub']], ['isDeleted', false]])->first();
                if ($user) {
                    return $next($request);
                } else {
                    return response('Unauthorized.', 403);
                }
            } else {
                return response('Unauthorized. Expired token', 403);
            }
        } else {
            return response('Unauthorized Request.', 403);
        }
    }
}
