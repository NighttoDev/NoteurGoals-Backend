<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// Các routes công khai - không yêu cầu xác thực
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Social Login Routes
Route::prefix('auth')->group(function () {
    // Thêm middleware web cho các routes liên quan đến OAuth
    Route::middleware(['web'])->group(function () {
        Route::get('/google/url', [AuthController::class, 'getGoogleAuthUrl']);
        Route::get('/facebook/url', [AuthController::class, 'getFacebookAuthUrl']);
        // Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
        // Route::get('/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
    });
    
    // // Các routes khác không cần session
    // Route::post('/social-login', [AuthController::class, 'socialLogin']);
    // Route::post('/google-id-token', [AuthController::class, 'googleIdTokenLogin']);
    // Route::post('/social-login-simple', [AuthController::class, 'simpleSocialLogin']);
});

// Routes yêu cầu xác thực
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Goal management
    Route::get('/goals', [\App\Http\Controllers\Goal\GoalController::class, 'index']);
    Route::post('/goals', [\App\Http\Controllers\Goal\GoalController::class, 'store']);
    Route::get('/goals/{goal}', [\App\Http\Controllers\Goal\GoalController::class, 'show']);
    Route::put('/goals/{goal}', [\App\Http\Controllers\Goal\GoalController::class, 'update']);
    Route::delete('/goals/{goal}', [\App\Http\Controllers\Goal\GoalController::class, 'destroy']);
    Route::post('/goals/{goal}/collaborators', [\App\Http\Controllers\Goal\GoalController::class, 'addCollaborator']);
    Route::delete('/goals/{goal}/collaborators/{userId}', [\App\Http\Controllers\Goal\GoalController::class, 'removeCollaborator']);
    Route::put('/goals/{goal}/share', [\App\Http\Controllers\Goal\GoalController::class, 'updateShareSettings']);

    // Notes
    Route::get('/notes', [\App\Http\Controllers\Note\NoteController::class, 'index']);
    Route::post('/notes', [\App\Http\Controllers\Note\NoteController::class, 'store']);
    Route::get('/notes/{note}', [\App\Http\Controllers\Note\NoteController::class, 'show']);
    Route::put('/notes/{note}', [\App\Http\Controllers\Note\NoteController::class, 'update']);
    Route::delete('/notes/{note}', [\App\Http\Controllers\Note\NoteController::class, 'destroy']);

    // Events
    Route::get('/events', [\App\Http\Controllers\Event\EventController::class, 'index']);
    Route::post('/events', [\App\Http\Controllers\Event\EventController::class, 'store']);
    Route::get('/events/{event}', [\App\Http\Controllers\Event\EventController::class, 'show']);
    Route::put('/events/{event}', [\App\Http\Controllers\Event\EventController::class, 'update']);
    Route::delete('/events/{event}', [\App\Http\Controllers\Event\EventController::class, 'destroy']);
});

// Thêm routes cho callbacks trực tiếp không phụ thuộc session
Route::get('/auth/google/callback-direct', [AuthController::class, 'handleGoogleCallbackDirect']);
Route::get('/auth/facebook/callback-direct', [AuthController::class, 'handleFacebookCallbackDirect']);

// Thêm các routes mới cho xác thực email
Route::get('/auth/verify/{id}/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerificationEmail']);
