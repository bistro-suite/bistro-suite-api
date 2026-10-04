<?php

use App\Http\Controllers\PublicMenuController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\BistroSettingsController;
use App\Http\Controllers\PublicOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/public/bistros/{bistro:slug}/menu', PublicMenuController::class);
    Route::post('/public/bistros/{bistro:slug}/orders', [PublicOrderController::class, 'store'])->middleware('throttle:8,1');

    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::prefix('admin')->middleware('auth:sanctum')->group(function (): void {
        Route::get('/settings', [BistroSettingsController::class, 'show']);
        Route::patch('/settings', [BistroSettingsController::class, 'update'])->middleware('admin.demo_read_only');
        Route::get('/orders', [OrderController::class, 'index']);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('admin.demo_read_only');
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products', [ProductController::class, 'store'])->middleware('admin.demo_read_only');
        Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('admin.demo_read_only');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('admin.demo_read_only');
        Route::post('/products/{product}/image', [ProductController::class, 'uploadImage'])->middleware('admin.demo_read_only');
    });
});
