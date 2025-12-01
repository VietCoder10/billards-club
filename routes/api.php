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


Route::post('login', \App\Http\Actions\Api\Auth\LoginAction::class);
Route::group([
    'middleware' => ['jwt.verify', 'auth.jwt'],
], function () {
    Route::get('user-info', \App\Http\Actions\Api\Auth\UserInfoAction::class);
    Route::get('logout', \App\Http\Actions\Api\Auth\LogoutAction::class);
});
