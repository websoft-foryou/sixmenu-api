<?php

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

Auth::routes();

Route::get('/', function () { return view('welcome'); });

Route::get('/signup', function () { return view('welcome'); });

Route::get('/admin/login', function () { return view('welcome'); });

Route::get('/admin/dashboard', function () { return view('welcome'); });

Route::get('/email-activate/{token}', function() { return view('welcome'); })->name('email-activate');

