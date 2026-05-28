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
use App\Http\Controllers\Admin\BuildingController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
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
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::post('dashboard/event', [DashboardController::class, 'event'])->name('dashboard.event');
        Route::post('dashboard/store', [DashboardController::class, 'store'])->name('dashboard.store');
        Route::delete('dashboard/{id}', [DashboardController::class, 'destroy'])->name('dashboard.destroy');
        Route::resource('user', UserController::class);
        Route::post('check-email', [UserController::class, 'checkEmail'])->name('user.checkEmail');
        Route::post('user/{id}/update-avatar', [UserController::class, 'updateAvatar'])->name('user.updateAvatar');
        Route::resource('supplier', \App\Http\Controllers\Admin\SuppliersController::class);
        Route::resource('order-item', \App\Http\Controllers\Admin\OrderItemController::class);
        Route::resource('order', OrderController::class);
        Route::put('order-session/{id}', [OrderController::class, 'updateSession'])->name('order.updateSession');
        Route::get('session', [OrderController::class, 'indexSession'])->name('order.indexSession');
        Route::resource('product', \App\Http\Controllers\Admin\ProductController::class);
        Route::post('product/{id}/update-avatar', [\App\Http\Controllers\Admin\ProductController::class, 'updateAvatar'])->name('product.updateAvatar');
        Route::resource('table', \App\Http\Controllers\Admin\TableController::class);
        Route::resource('table_price_master', \App\Http\Controllers\Admin\TablePriceMasterController::class);
        Route::resource('invoice', InvoiceController::class);
        Route::resource('tournament', \App\Http\Controllers\Admin\TournamentController::class);
        Route::post('tournament/{tournament}/participant/{participant}', [\App\Http\Controllers\Admin\TournamentController::class, 'updateParticipantStatus'])->name('tournament.participant.update');
        Route::post('customer/check-email', [\App\Http\Controllers\Admin\CustomerController::class, 'checkEmail'])->name('customer.checkEmail');
        Route::post('customer/{id}/update-avatar', [\App\Http\Controllers\Admin\CustomerController::class, 'updateAvatar'])->name('customer.updateAvatar');
        Route::get('customer/search-modal-customer', [\App\Http\Controllers\Admin\CustomerController::class, 'searchModalCustomer'])->name('customer.searchModalCustomer');
        Route::post('customer/store-modal-customer', [\App\Http\Controllers\Admin\CustomerController::class, 'storeModalCustomer'])->name('customer.storeModalCustomer');
        Route::resource('customer', \App\Http\Controllers\Admin\CustomerController::class);
        Route::resource('report', ReportController::class)->only(['index']);
    });
});
