<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\ResetPasswordController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\CountingController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\InternetBankingController;
use App\Http\Controllers\Admin\LedgerController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SendRequestController;
use App\Http\Controllers\Admin\SettingController;
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
    Route::resource('forgot-password', ForgotPasswordController::class);
    Route::resource('reset-password', ResetPasswordController::class);
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    Route::group([
        'middleware' => ['assign.guard:admin', 'admin'],
    ], function () {
        Route::resource('dashboard', DashboardController::class)->only(['index']);
        Route::resource('billing', BillingController::class);
        Route::resource('company', CompanyController::class);
        Route::resource('contract', ContractController::class);
        Route::resource('counting', CountingController::class);
        Route::resource('inquiry', InquiryController::class);
        Route::resource('internet-banking', InternetBankingController::class);
        Route::resource('ledger', LedgerController::class);
        Route::resource('maintenance', MaintenanceController::class);
        Route::resource('payment', PaymentController::class);
        Route::resource('send-request', SendRequestController::class);
        Route::resource('setting', SettingController::class);
        Route::resource('user', UserController::class);
        Route::post('check-email', [UserController::class, 'checkEmail'])->name('user.checkEmail');
    });
});
