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



Route::prefix('v1')->group(function(){

    //Protected
    Route::get('/user', 'Api\v1\UserController@showCurrent')->middleware('auth:api');
    Route::post('/logout', 'Api\v1\UserController@logout')->middleware('auth:api');
    Route::post('/changepass', 'Api\v1\UserController@changePassword')->middleware('auth:api');

    //Resource
    Route::resource('/roles', 'Api\v1\RoleController')->middleware('auth:api');
    Route::resource('/users', 'Api\v1\UserController')->middleware('auth:api');

    //User Specific
    Route::post('/login', 'Api\v1\UserController@login')->name('users.api_login');
    Route::post('/register', 'Api\v1\UserController@register')->name('users.api_register'); 
});