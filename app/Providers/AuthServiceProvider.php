<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    
    public function register()
    {
        //
    }
    
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        
        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->bearerToken('token')) {
                $pub_key = file_get_contents(dirname(dirname(__FILE__)).'../../public.pem');

                $jwt = $request->bearerToken('token');
                // print_r($request->bearerToken('token'));
                $decoded = JWT::decode($jwt, $pub_key, array('RS256'));
                
                /*
                NOTE: This will now be an object instead of an associative array. To get
                an associative array, you will need to cast it as such:
                */
                
                $decoded_array = (array) $decoded;
                // print_r($decoded_array);

                $user = User::where('email', $decoded_array['sub']) -> first();
                if (app('hash')->check($decoded_array['pass'], $user -> password)) {
                    return $user;
                }
                
                // return User::where([['email', $decoded_array['sub']], ['password', app('hash') -> make($decoded_array['pass'])]])-> first();
            }
        });
    }
}
