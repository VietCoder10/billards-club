<?php

use App\Http\Controllers\User\LandingController;
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


use App\Http\Controllers\User\Auth\LoginController;
use App\Http\Controllers\User\Auth\RegisterController;
use App\Http\Controllers\User\Auth\ForgotPasswordController;
use App\Http\Controllers\User\Auth\ResetPasswordController;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::group(['as' => 'user.'], function () {
    Route::resource('login', LoginController::class)->only(['index', 'store']);
    Route::resource('register', RegisterController::class)->only(['index', 'store']);
    Route::resource('forgot-password', ForgotPasswordController::class)->only(['index', 'store']);
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'show'])->name('reset-password.show');
    Route::post('reset-password/{token}', [ResetPasswordController::class, 'store'])->name('reset-password.store');
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
});
