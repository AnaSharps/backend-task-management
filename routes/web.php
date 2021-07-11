<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/user', 'AuthController@genjwt');
$router->get('/allUsers', 'AuthController@getUsers');
$router->post('/register/verify', 'AuthController@registerUser');
$router->get('/verifyEmail', ['as' => 'verification', 'uses' => 'AuthController@verifyEmail']);
$router->get('/register/signup', 'AuthController@signup');
$router->post('/login', 'AuthController@login');
$router->get('/test/{token}', 'AuthController@test');

$router->group(['prefix' => 'api', 'middleware' => 'auth'],  function () use ($router) {
    $router->get('/posts', 'PostController@index');
    $router->post('/posts', 'PostController@store'); 
    $router->put('/posts/{id}', 'PostController@update');
    $router->delete('/posts/{id}', 'PostController@destroy');
});
