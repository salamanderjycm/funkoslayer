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
});

// Public Products API
Route::apiResource('products', ProductController::class, ['only' => ['index', 'show']]);

// Public Categories API
Route::apiResource('categories', CategoryController::class, ['only' => ['index', 'show']]);

// Payment routes (Checkout API - Transparente)
Route::post('/payment/process', [PaymentController::class, 'processPayment'])->name('payment.process');
Route::get('/payment/public-key', [PaymentController::class, 'getPublicKey'])->name('payment.public-key');

// Webhooks (public for MercadoPago)
Route::post('/payment/webhook', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('categories', CategoryController::class);
    });
});