<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\PaymentController;

// Auth endpoints (public)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected auth endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/my-orders', [App\Http\Controllers\OrderController::class, 'myOrders']);
});

// Public Products API
Route::apiResource('products', ProductController::class, ['only' => ['index', 'show']]);

// Public Categories API
Route::apiResource('categories', CategoryController::class, ['only' => ['index', 'show']]);

// Payment routes (Checkout Pro - Redirección)
Route::post('/payment/preference', [PaymentController::class, 'createPreference'])->name('payment.preference');

// Webhooks (public for MercadoPago)
Route::post('/payment/webhook', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('categories', CategoryController::class);
    });
});