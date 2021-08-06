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

// $router->get('/posts', 'PostController@index');
// $router->post('/posts', 'PostController@store'); 
// $router->put('/posts/{id}', 'PostController@update');
// $router->delete('/posts/{id}', 'PostController@destroy');
// $router->post('/user', 'AuthController@genjwt');
// $router->post('/admin/addUser', ['middleware' => 'role:Admin', 'AuthController@addUser']);

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Authentication Routes
$router->post('/register', 'RegistrationController@registerSelf');
$router->get('/verifyEmail', ['as' => 'verification', 'uses' => 'EmailController@verifyEmail']);
$router->post('/register/signup', 'RegistrationController@signup');
$router->post('/login', 'LoginController@login');
$router->post('/forgotPass', 'PasswordController@forgotPass');
$router->post('/resetPass', 'PasswordController@resetPass');

// Admin Routes
$router->group(['prefix' => 'admin', 'middleware' => 'admin'], function () use ($router) {
    $router->get('/', 'AuthController@redirect');
    $router->post('/addUser', 'AdminController@addUser');
    $router->post('/deleteUser', 'AdminController@deleteUser');
});

// Authenticated Routes
$router->group(['prefix' => 'api', 'middleware' => 'auth'],  function () use ($router) {
    $router->get('/', 'AuthController@redirect');
    $router->post('/allUsers', 'AuthController@getUsers');
    $router->delete('deleteSelf', 'DeRegisterController@deRegister');
    $router->post('/changePassword', 'PasswordController@changePassword');
    $router->get('/logout', 'LoginController@logout');

    ///////////////////////////////////////////////////////////////////////////////
    $router->post('/createTask', 'TaskController@createTask');
    $router->post('/getTasks', 'TaskController@getTasks');
    $router->put('/updateTaskStatus', 'TaskController@updateStatus');
    $router->delete('/deleteTask', 'TaskController@deleteTask');
    $router->post('/viewTask/{id}', 'TaskController@viewTask');
    $router->get('/myStats', 'DashboardController@myStats');
});
