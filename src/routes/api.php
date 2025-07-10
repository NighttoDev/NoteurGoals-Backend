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


// =======================================================
// --- PROTECTED ROUTES ---
// Yêu cầu xác thực qua Sanctum (phải có token)
// =======================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- USER & AUTH ---
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

    // --- GOALS & MILESTONES ---
    Route::apiResource('goals', GoalController::class);
    Route::post('/goals/{goal}/collaborators', [GoalController::class, 'addCollaborator']);
    Route::delete('/goals/{goal}/collaborators/{userId}', [GoalController::class, 'removeCollaborator']);
    Route::put('/goals/{goal}/share', [GoalController::class, 'updateShareSettings']);
    Route::apiResource('goals.milestones', MilestoneController::class)->shallow();

    // --- NOTES ---
    Route::apiResource('notes', NoteController::class);
    Route::post('/notes/{note}/goals', [NoteController::class, 'linkGoal']);
    Route::delete('/notes/{note}/goals/{goalId}', [NoteController::class, 'unlinkGoal']);
    Route::post('/notes/{note}/milestones', [NoteController::class, 'linkMilestone']);
    Route::delete('/notes/{note}/milestones/{milestoneId}', [NoteController::class, 'unlinkMilestone']);

    // --- EVENTS ---
    Route::apiResource('events', EventController::class);
    Route::post('/events/{event}/goals', [EventController::class, 'linkGoal']);
    Route::delete('/events/{event}/goals/{goalId}', [EventController::class, 'unlinkGoal']);

    // --- FILES ---
    Route::apiResource('files', FileController::class)->except(['update']);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::post('/files/{file}/goals', [FileController::class, 'linkGoal']);
    Route::delete('/files/{file}/goals/{goalId}', [FileController::class, 'unlinkGoal']);
    Route::post('/files/{file}/notes', [FileController::class, 'linkNote']);
    Route::delete('/files/{file}/notes/{noteId}', [FileController::class, 'unlinkNote']);

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

    // --- AI SUGGESTIONS ---
    Route::prefix('ai-suggestions')->controller(AISuggestionController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/type/{type}', 'getByType');
        Route::get('/unread/count', 'getUnreadCount');
        Route::post('/read-all', 'markAllAsRead');
        Route::get('/{suggestion}', 'show');
        Route::post('/{suggestion}/read', 'markAsRead');
        Route::post('/{suggestion}/unread', 'markAsUnread');
        Route::post('/{suggestion}/goals', 'linkGoal');
        Route::delete('/{suggestion}/goals/{goalId}', 'unlinkGoal');
    });
});


// =======================================================
// --- PUBLIC CALLBACK ROUTES ---
// =======================================================

// Route IPN vẫn giữ lại để dùng sau này.
Route::any('/payment/vnpay/callback', [PaymentController::class, 'handleVnPayCallback'])->name('payment.vnpay_ipn_callback');