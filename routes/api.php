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


Route::group(['namespace'=> 'App\Http\Controllers\Auth'], function() {
    Route::post('add_user', 'RegisterController@user_register');
    Route::post('verify_email', 'RegisterController@verify_email');
    Route::post('login_user', 'LoginController@login_user');
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
