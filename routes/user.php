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

Route::group([
    'as' => 'user.'
], function () {
    Route::resource('login', LoginController::class);
    Route::resource('forgot-password', ForgotPasswordController::class);
    Route::resource('reset-password', ResetPasswordController::class);
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    Route::resource('register', RegisterController::class);
    Route::group([
        'middleware' => 'assign.guard:customer',
    ], function () {
        Route::get('dashboard', [\App\Http\Controllers\User\DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('table', [\App\Http\Controllers\User\TableController::class, 'index'])->name('table.index');
        Route::get('tournament', [\App\Http\Controllers\User\TournamentController::class, 'index'])->name('tournament.index');
        Route::get('tournament/{id}', [\App\Http\Controllers\User\TournamentController::class, 'show'])->name('tournament.show');
        Route::post('tournament/{id}/register', [\App\Http\Controllers\User\TournamentController::class, 'register'])->name('tournament.register');
    });
});
