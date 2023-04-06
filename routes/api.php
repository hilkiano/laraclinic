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
            $router->group(['prefix' => 'menus'], function () use ($router) {
                $router->post('/change-state', '\App\Http\Controllers\Web\MenusController@changeState');
                $router->post('/save', '\App\Http\Controllers\Web\MenusController@save');
            });
            $router->group(['prefix' => 'groups'], function () use ($router) {
                $router->post('/change-state', '\App\Http\Controllers\Web\GroupsController@changeState');
                $router->post('/save', '\App\Http\Controllers\Web\GroupsController@save');
            });
            $router->group(['prefix' => 'roles'], function () use ($router) {
                $router->post('/change-state', '\App\Http\Controllers\Web\RolesController@changeState');
                $router->post('/save', '\App\Http\Controllers\Web\RolesController@save');
            });
        });
        // patient
        $router->group(['prefix' => 'patient'], function () use ($router) {
            $router->post('save-info', '\App\Http\Controllers\Web\PatientFormController@save');
            $router->post('add-potrait', '\App\Http\Controllers\Web\PatientFormController@addPotrait');
            $router->get('get-potraits/{patientId}', '\App\Http\Controllers\Web\PatientFormController@getPotraits');
        });
        // appointment
        $router->group(['prefix' => 'appointment'], function () use ($router) {
            $router->post('make', '\App\Http\Controllers\Web\AppointmentController@make');
            $router->get('patient-list/{query}', '\App\Http\Controllers\Web\PatientListController@selectList');
            $router->post('get-complete-list', '\App\Http\Controllers\Web\AppointmentController@getCompleteList');
            $router->get('get-detail/{uuid}', '\App\Http\Controllers\Api\AppointmentApi@getDetail');
            $router->post('make-detail', '\App\Http\Controllers\Api\AppointmentApi@makeDetail');
            $router->get('mine', '\App\Http\Controllers\Api\AppointmentApi@getMyAppointments');
            $router->post('take', '\App\Http\Controllers\Api\AppointmentApi@take');
            $router->post('progress', '\App\Http\Controllers\Api\AppointmentApi@progress');
            $router->post('get-assignation', '\App\Http\Controllers\Api\AppointmentApi@getAssignation');
            $router->get('item-list/{query}', '\App\Http\Controllers\Api\AppointmentApi@getItems');
        });
        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->post('save-configs', '\App\Http\Controllers\Web\UsersController@saveConfigs');
        });
        // medical records
        $router->group(['prefix' => 'records'], function () use ($router) {
            $router->get('prescription/{id}', '\App\Http\Controllers\Api\MedicalRecordApi@getPrescription');
        });
        // medicines
        $router->group(['prefix' => 'medicines'], function () use ($router) {
            $router->post('list', '\App\Http\Controllers\Api\MedicinesApi@list');
            $router->post('save', '\App\Http\Controllers\Api\MedicinesApi@save');
            $router->post('delete-restore', '\App\Http\Controllers\Api\MedicinesApi@deleteRestore');
        });
        // services
        $router->group(['prefix' => 'services'], function () use ($router) {
            $router->post('list', '\App\Http\Controllers\Api\ServicesApi@list');
            $router->post('save', '\App\Http\Controllers\Api\ServicesApi@save');
            $router->post('delete-restore', '\App\Http\Controllers\Api\ServicesApi@deleteRestore');
        });
    });
});
