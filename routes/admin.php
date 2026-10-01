<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('cities', CityController::class)->except('show');
        Route::patch('cities/{city}/toggle', [CityController::class, 'toggle'])->name('cities.toggle');

        Route::resource('categories', CategoryController::class)->except('show');
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

        Route::resource('payment-methods', PaymentMethodController::class)->except('show');
        Route::patch('payment-methods/{payment_method}/toggle', [PaymentMethodController::class, 'toggle'])
            ->name('payment-methods.toggle');

        Route::resource('settings', SettingController::class)->except('show');

        Route::resource('banners', BannerController::class)->except('show');

        Route::resource('contact-messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);
    });
});
