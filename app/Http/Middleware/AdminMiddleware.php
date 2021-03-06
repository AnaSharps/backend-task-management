<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use App\Models\User;
use App\Helper\GenerateJWT;

class AdminMiddleware
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
        // Pre-Middleware Action
        if ($request->cookie('token')) {

            $jwt = $request->cookie('token');

            $decoded = (new GenerateJWT)->decodejwt($jwt); //expire exception

            if (gettype($decoded) === "array") {
                $user = User::where(['email' => $decoded['sub'], 'isDeleted' => false])->first();
                if ($user && $user->role === "ADMIN" && !($user->isDeleted)) {
                    // $response = $next($request);
                    // Post-Middleware Action

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
