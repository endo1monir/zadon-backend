<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SocialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorProductController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('cities', CityController::class)->except('show');
        Route::patch('cities/{city}/toggle', [CityController::class, 'toggle'])->name('cities.toggle');

        Route::resource('categories', CategoryController::class)->except('show');
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

        Route::resource('socials', SocialController::class)->except('show');

        Route::resource('payment-methods', PaymentMethodController::class)->except('show');
        Route::patch('payment-methods/{payment_method}/toggle', [PaymentMethodController::class, 'toggle'])
            ->name('payment-methods.toggle');

        Route::resource('settings', SettingController::class)->except('show');

        Route::resource('banners', BannerController::class)->except('show');

        Route::resource('contact-messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);

        Route::bind('admin', fn (string $id): User => User::admin()->whereKey($id)->firstOrFail());

        Route::resource('admins', AdminController::class)->except('show');
        Route::patch('admins/{admin}/toggle', [AdminController::class, 'toggle'])->name('admins.toggle');

        Route::bind('user', fn (string $id): User => User::customer()->whereKey($id)->firstOrFail());

        Route::resource('users', UserController::class)->except('show');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::get('users/{user}/addresses', [UserController::class, 'addresses'])->name('users.addresses');

        Route::bind('vendor', fn (string $id): User => User::vendor()->whereKey($id)->firstOrFail());

        Route::resource('vendors', VendorController::class)->except('show');
        Route::patch('vendors/{vendor}/toggle', [VendorController::class, 'toggle'])->name('vendors.toggle');

        Route::prefix('vendors/{vendor}/products')->name('vendors.products.')->group(function (): void {
            Route::get('/', [VendorProductController::class, 'index'])->name('index');
            Route::get('/create', [VendorProductController::class, 'create'])->name('create');
            Route::post('/', [VendorProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [VendorProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [VendorProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [VendorProductController::class, 'destroy'])->name('destroy');
            Route::patch('/{product}/toggle', [VendorProductController::class, 'toggle'])->name('toggle');
        });

        Route::resource('reviews', ReviewController::class)->only(['index', 'destroy']);
        Route::patch('reviews/{review}/toggle', [ReviewController::class, 'toggle'])->name('reviews.toggle');

        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications', [NotificationController::class, 'store'])->name('notifications.store');
        Route::post('notifications/users/{user}', [NotificationController::class, 'sendToUser'])
            ->name('notifications.user');
        Route::post('notifications/vendors/{vendor}', [NotificationController::class, 'sendToUser'])
            ->name('notifications.vendor');
    });
});
