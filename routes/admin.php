<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\UserController;
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

Route::group([
    'as' => 'admin.',
], function () {
    Route::get('/', [LoginController::class, 'index']);
    Route::resource('login', LoginController::class);
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    Route::group([
        'middleware' => ['assign.guard:admin', 'admin'],
    ], function () {
        Route::resource('dashboard', DashboardController::class)->only(['index']);
        Route::resource('user', UserController::class);
        Route::post('check-email', [UserController::class, 'checkEmail'])->name('user.checkEmail');
    });
});
