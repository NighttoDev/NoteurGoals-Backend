<?php
// filepath: c:\xampp\htdocs\NoteurGoals-Backend\src\routes\api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// Các routes công khai - không yêu cầu xác thực
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Social Login Routes
Route::prefix('auth')->group(function () {
    // Thêm middleware web cho các routes liên quan đến OAuth
    Route::middleware(['web'])->group(function () {
        Route::get('/google/url', [AuthController::class, 'getGoogleAuthUrl']);
        Route::get('/facebook/url', [AuthController::class, 'getFacebookAuthUrl']);
        Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
        Route::get('/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
    });
    
    // Các routes khác không cần session
    Route::post('/social-login', [AuthController::class, 'socialLogin']);
    Route::post('/google-id-token', [AuthController::class, 'googleIdTokenLogin']);
    Route::post('/social-login-simple', [AuthController::class, 'simpleSocialLogin']);
});

// Route test
Route::get('/hello', function () {
    return ['message' => 'Xin chào từ API Laravel!'];
});

// Routes yêu cầu xác thực
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Thêm routes cho callbacks trực tiếp không phụ thuộc session
Route::get('/auth/google/callback-direct', [AuthController::class, 'handleGoogleCallbackDirect']);
Route::get('/auth/facebook/callback-direct', [AuthController::class, 'handleFacebookCallbackDirect']);
