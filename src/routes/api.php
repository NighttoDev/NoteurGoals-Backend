<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Controllers ---
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Goal\GoalController;
use App\Http\Controllers\Note\NoteController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\File\FileController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Milestone\MilestoneController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Friendship\FriendshipController;
use App\Http\Controllers\AISuggestion\AISuggestionController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Payment\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =======================================================
// --- PUBLIC & AUTHENTICATION ROUTES ---
// =======================================================

// --- Standard Auth ---
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/verify-email', 'verifyEmail');
    Route::post('/resend-verification-email', 'resendVerificationEmail');
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/reset-password', 'resetPassword');
    Route::get('/auth/google/callback-direct', 'handleGoogleCallbackDirect');
    Route::get('/auth/facebook/callback-direct', 'handleFacebookCallbackDirect');
});


// --- Social Login Callbacks (Stateless) ---
// Đặt ở ngoài group middleware để không yêu cầu xác thực
Route::get('/auth/google/callback-direct', [AuthController::class, 'handleGoogleCallbackDirect']);
Route::get('/auth/facebook/callback-direct', [AuthController::class, 'handleFacebookCallbackDirect']);


// =======================================================
// --- PROTECTED ROUTES (Require sanctum authentication) ---
// =======================================================
// Routes yêu cầu xác thực
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']); 
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::get('/profile', 'getProfile');
        Route::post('/profile/update', 'updateProfile');
        Route::post('/password/change', 'changePassword');
        Route::post('/account/delete', 'deleteAccount');
    });

    // --- SUBSCRIPTION MANAGEMENT ---
    Route::prefix('subscriptions')->controller(SubscriptionController::class)->group(function() {
        Route::get('/plans', 'plans');
        Route::get('/my-current', 'myCurrentSubscription');
        Route::post('/cancel/{subscription}', 'cancel');
        Route::get('/plans/{plan}', 'show');
    });

    // --- PAYMENT ROUTES ---
    Route::prefix('payment')->controller(PaymentController::class)->group(function() {
        Route::post('/vnpay/create', 'createVnPayPayment');

        // *** ĐÂY LÀ ROUTE MỚI ĐƯỢC THÊM VÀO ***
        // Route này sẽ được frontend gọi sau khi người dùng quay về từ VNPay.
        Route::post('/vnpay/verify-return', 'verifyReturnData');
    });
    
    // --- FRIENDSHIPS ---
    Route::prefix('friends')->controller(FriendshipController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/request', 'sendRequest');
        Route::post('/{friendship}/respond', 'respond');
        Route::delete('/{friendship}', 'destroy');
    });

    // --- NOTIFICATIONS ---
    Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/notification}/read', 'markAsRead');
        Route::delete('/{notification}', 'destroy');
    });

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

    // Milestones
    Route::get('/goals/{goal}/milestones', [\App\Http\Controllers\Milestone\MilestoneController::class, 'index']);
    Route::post('/goals/{goal}/milestones', [\App\Http\Controllers\Milestone\MilestoneController::class, 'store']);
    Route::get('/milestones/{milestone}', [\App\Http\Controllers\Milestone\MilestoneController::class, 'show']);
    Route::put('/milestones/{milestone}', [\App\Http\Controllers\Milestone\MilestoneController::class, 'update']);
    Route::delete('/milestones/{milestone}', [\App\Http\Controllers\Milestone\MilestoneController::class, 'destroy']);

    // Files
    Route::get('/files', [\App\Http\Controllers\File\FileController::class, 'index']);
    Route::post('/files', [\App\Http\Controllers\File\FileController::class, 'store']);
    Route::get('/files/{file}', [\App\Http\Controllers\File\FileController::class, 'show']);
    Route::delete('/files/{file}', [\App\Http\Controllers\File\FileController::class, 'destroy']);


});
// =======================================================
// --- PUBLIC CALLBACK ROUTES ---
// =======================================================

// Route IPN vẫn giữ lại để dùng sau này.
Route::any('/payment/vnpay/callback', [PaymentController::class, 'handleVnPayCallback'])->name('payment.vnpay_ipn_callback');