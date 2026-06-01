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

// Public Products API (customers can view)
Route::apiResource('products', ProductController::class, ['only' => ['index', 'show']]);

// Public Categories API
Route::apiResource('categories', CategoryController::class, ['only' => ['index', 'show']]);

// Payment routes
Route::post('/payment/preference', [PaymentController::class, 'createPreference'])->name('payment.preference');
Route::post('/payment/create', [PaymentController::class, 'createPayment'])->name('payment.create');
Route::get('/payment/{paymentId}/status', [PaymentController::class, 'getPaymentStatus'])->name('payment.status');
Route::get('/payment/public-key', [PaymentController::class, 'getPublicKey'])->name('payment.public-key');

// Payment callbacks (used by MercadoPago)
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/pending', [PaymentController::class, 'pending'])->name('payment.pending');
Route::get('/payment/failure', [PaymentController::class, 'failure'])->name('payment.failure');

// Webhooks (public for MercadoPago)
Route::post('/payment/webhook', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');

// Admin routes (only for admin users)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('categories', CategoryController::class);
    });
});
