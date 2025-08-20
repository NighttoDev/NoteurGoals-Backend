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
use App\Http\Controllers\Api\MessageController;

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
Route::get('/auth/google/callback-direct', [AuthController::class, 'handleGoogleCallbackDirect']);
Route::get('/auth/facebook/callback-direct', [AuthController::class, 'handleFacebookCallbackDirect']);


// =======================================================
// --- PROTECTED ROUTES (Require sanctum authentication) ---
// =======================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']); 
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::get('/profile', 'getProfile');
        Route::post('/profile/update', 'updateProfile');
        Route::post('/password/change', 'changePassword');
        Route::post('/account/delete', 'deleteAccount');
    });

    // --- SUBSCRIPTION & PAYMENT ---
    Route::prefix('subscriptions')->controller(SubscriptionController::class)->group(function() {
        Route::get('/plans', 'plans');
        Route::get('/my-current', 'myCurrentSubscription');
        Route::post('/cancel/{subscription}', 'cancel');
        Route::get('/plans/{plan}', 'show');
    });

    Route::prefix('payment')->controller(PaymentController::class)->group(function() {
        Route::post('/vnpay/create', 'createVnPayPayment');
        Route::post('/vnpay/verify-return', 'verifyReturnData');
    });

    Route::get('/users/search', [FriendshipController::class, 'searchUsers']);
    Route::get('/users/suggestions', [FriendshipController::class, 'getUserSuggestions']);
    
    Route::prefix('friends')->controller(FriendshipController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/request', 'sendRequest');
        Route::post('/request/id', 'sendRequestById');
        Route::post('/{friendship}/respond', 'respond');
        Route::delete('/{friendship}', 'destroy');
    });

    Route::get('/collaborators', [FriendshipController::class, 'getCollaborators']);

     // --- MESSAGES ---
    Route::prefix('messages')->controller(MessageController::class)->group(function () {
        Route::get('/{friendId}', 'index'); // Lấy lịch sử chat với 1 user bằng ID
        Route::post('/', 'store');         // Gửi tin nhắn mới
    });

    // --- NOTIFICATIONS ---
    Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/notification}/read', 'markAsRead'); // Có thể có lỗi typo ở đây, nên là '/{notification}/read'
        Route::delete('/{notification}', 'destroy');
    });

    // Goal management
    Route::get('/goals', [GoalController::class, 'index']);
    Route::post('/goals', [GoalController::class, 'store']);
    Route::get('/goals/{goal}', [GoalController::class, 'show']);
    Route::put('/goals/{goal}', [GoalController::class, 'update']);
    Route::delete('/goals/{goal}', [GoalController::class, 'destroy']);
    Route::post('/goals/{goal}/collaborators', [GoalController::class, 'addCollaborator']);
    Route::delete('/goals/{goal}/collaborators/{userId}', [GoalController::class, 'removeCollaborator']);
    Route::put('/goals/{goal}/share', [GoalController::class, 'updateShareSettings']);

    // Notes
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/notes/{note}', [NoteController::class, 'show']);
    Route::put('/notes/{note}', [NoteController::class, 'update']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);

    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);

    // Milestones
    Route::get('/goals/{goal}/milestones', [MilestoneController::class, 'index']);
    Route::post('/goals/{goal}/milestones', [MilestoneController::class, 'store']);
    Route::get('/milestones/{milestone}', [MilestoneController::class, 'show']);
    Route::put('/milestones/{milestone}', [MilestoneController::class, 'update']);
    Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy']);

    // Files
    Route::get('/files', [FileController::class, 'index']);
    Route::post('/files', [FileController::class, 'store']);
    Route::get('/files/{file}', [FileController::class, 'show']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);

});

// =======================================================
// --- PUBLIC CALLBACK ROUTES ---
// =======================================================

Route::any('/payment/vnpay/callback', [PaymentController::class, 'handleVnPayCallback'])->name('payment.vnpay_ipn_callback');