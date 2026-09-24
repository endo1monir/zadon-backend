<?php

use App\Http\Controllers\Api\Vendor\AuthController;
use App\Http\Controllers\Api\Vendor\DashboardController;
use App\Http\Controllers\Api\Vendor\NotificationController;
use App\Http\Controllers\Api\Vendor\OrderController;
use App\Http\Controllers\Api\Vendor\ProductController;
use App\Http\Controllers\Api\Vendor\ReviewController;
use App\Http\Controllers\Api\Vendor\StoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor / Store Dashboard API
|--------------------------------------------------------------------------
| JSON API used by the vendor store dashboard. The file is grouped under
| the /api/vendor prefix in bootstrap/app.php.
*/

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:login');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'abilities:vendor'])->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/dashboard/overview', [DashboardController::class, 'overview']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}/stock-adjustments', [ProductController::class, 'stockHistory']);
    Route::post('/products/{product}/stock-adjustments', [ProductController::class, 'adjustStock']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::patch('/orders/{order}/items/{orderItem}/packed', [OrderController::class, 'setPacked'])->scopeBindings();

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply']);

    Route::get('/store', [StoreController::class, 'show']);
    Route::put('/store', [StoreController::class, 'update']);
    Route::patch('/store/status', [StoreController::class, 'updateStatus']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});