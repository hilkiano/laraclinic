<?php

use App\Events\AssignmentCreated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', '\App\Http\Controllers\Web\LoginController@index');

Route::group(['middleware' => 'web.auth.jwt'], function () use ($router) {
    $router->get('/', '\App\Http\Controllers\Web\DashboardController@index');
    $router->get('/user-configs/{id}', '\App\Http\Controllers\Web\UsersController@viewConfigs');

    $router->group(['prefix' => 'master'], function () use ($router) {
        $router->get('users', '\App\Http\Controllers\Web\UsersController@index');
        $router->get('menus', '\App\Http\Controllers\Web\MenusController@index');
        $router->get('privileges', '\App\Http\Controllers\Web\PrivilegesController@index');
        $router->get('roles', '\App\Http\Controllers\Web\RolesController@index');
        $router->get('groups', '\App\Http\Controllers\Web\GroupsController@index');
    });

    $router->group(['prefix' => 'patient'], function () use ($router) {
        $router->get('list', '\App\Http\Controllers\Web\PatientListController@index');
        $router->get('register', '\App\Http\Controllers\Web\PatientFormController@index');
        $router->get('update/{id}', '\App\Http\Controllers\Web\PatientFormController@update');
    });

    $router->group(['prefix' => 'appointments'], function () use ($router) {
        $router->get('list', '\App\Http\Controllers\Web\AppointmentController@index');
        $router->get('assignment', '\App\Http\Controllers\Web\AppointmentController@viewAssignment');
        $router->get('complete-list', '\App\Http\Controllers\Web\AppointmentController@viewCompleteList');
        $router->get('detail/{uuid}', '\App\Http\Controllers\Web\AppointmentController@viewDetail');
        $router->get('detail_blank/{uuid}', '\App\Http\Controllers\Web\AppointmentController@viewDetail');
    });

    $router->group(['prefix' => 'medicines'], function () use ($router) {
        $router->get('/', '\App\Http\Controllers\Web\MedicinesController@index');
    });

    $router->group(['prefix' => 'services'], function () use ($router) {
        $router->get('/', '\App\Http\Controllers\Web\ServicesController@index');
    });

    $router->group(['prefix' => 'cashier'], function () use ($router) {
        $router->get('/', '\App\Http\Controllers\Web\CashierController@index');
    });
});
