<?php

use App\Http\Controllers\Api\App\AddressController;
use App\Http\Controllers\Api\App\AuthController;
use App\Http\Controllers\Api\App\CartController;
use App\Http\Controllers\Api\App\CategoryController;
use App\Http\Controllers\Api\App\CheckoutController;
use App\Http\Controllers\Api\App\CityController;
use App\Http\Controllers\Api\App\ContactController;
use App\Http\Controllers\Api\App\HomeController;
use App\Http\Controllers\Api\App\NotificationController;
use App\Http\Controllers\Api\App\OrderController;
use App\Http\Controllers\Api\App\PaymentMethodController;
use App\Http\Controllers\Api\App\PolicyController;
use App\Http\Controllers\Api\App\ProductController;
use App\Http\Controllers\Api\App\ProfileController;
use App\Http\Controllers\Api\App\ReviewController;
use App\Http\Controllers\Api\App\SocialController;
use App\Http\Controllers\Api\App\StoreController;
use App\Http\Controllers\Api\App\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer App API
|--------------------------------------------------------------------------
| Undocumented public-facing JSON API used by the customer mobile app.
| All routes are namespaced under /api (registered in bootstrap/app.php).
*/

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:login');

Route::get('/home', [HomeController::class, 'index']);
Route::get('/cities', [CityController::class, 'index']);
Route::get('/socials', [SocialController::class, 'index']);
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
Route::get('/policy', [PolicyController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/{store}/products', [StoreController::class, 'products']);
Route::get('/stores/{store}', [StoreController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/complete-profile', [AuthController::class, 'completeProfile']);

    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::post('/contact-us', [ContactController::class, 'store']);
    Route::post('/categories/{category}/icon', [CategoryController::class, 'uploadIcon']);
    Route::post('/socials/{social}/icon', [SocialController::class, 'uploadIcon']);
    Route::post('/payment-methods/{paymentMethod}/icon', [PaymentMethodController::class, 'uploadIcon']);

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::post('/addresses/{address}/default', [AddressController::class, 'setDefault']);
    Route::patch('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'removeItem']);

    Route::post('/checkout', [CheckoutController::class, 'store']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/{order}/review', [ReviewController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    Route::get('/wallet', [WalletController::class, 'show']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('/wallet/top-up', [WalletController::class, 'topUp']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read']);
});
