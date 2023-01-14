<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () use ($router) {
    $router->post('/login', \App\Http\Controllers\Auth\LoginController::class)->name('login');

    // guarded API. Must have JWT token.
    $router->group(['middleware' => 'auth.jwt'], function () use ($router) {
        $router->get('/me', \App\Http\Controllers\Auth\UserController::class)->name('me');
        $router->post('/logout', \App\Http\Controllers\Auth\LogoutController::class)->name('logout');
        // master
        $router->group(['prefix' => 'master'], function () use ($router) {
            $router->group(['prefix' => 'users'], function () use ($router) {
                $router->post('/change-state', '\App\Http\Controllers\Web\UsersController@changeState');
                $router->post('/save', '\App\Http\Controllers\Web\UsersController@save');
            });
        });
    });
});
