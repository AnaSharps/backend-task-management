<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Factory as Auth;

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
            $key = env('KEY_JWT');

            $jwt = $request->bearerToken('token');

            try {
                $decoded = JWT::decode($jwt, $key, array('HS256'));

                $user = User::where('Email', $decoded->sub)->first();
                if ($user) {
                    return $next($request);
                } else {
                    return response('Unauthorized.', 401);
                }
            } catch (\Exception $e) {
                return response($e->getMessage(), 500);
            }
        }
    }
}
