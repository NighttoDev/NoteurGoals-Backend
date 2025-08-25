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
        Route::post('/renew', 'renew');
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
        Route::post('/{notification}/read', 'markAsRead');
        Route::delete('/{notification}', 'destroy');
    });

    // Goal management
    Route::get('/goals', [GoalController::class, 'index']);
    Route::post('/goals', [GoalController::class, 'store']);
    Route::get('/goals/trash', [GoalController::class, 'trashed'])->name('goals.trashed');
    Route::get('/goals/{goal}', [GoalController::class, 'show']);
    Route::put('/goals/{goal}', [GoalController::class, 'update']);
    Route::delete('/goals/{goal}', [GoalController::class, 'destroy']);

    Route::post('/goals/{goal}/collaborators', [GoalController::class, 'addCollaborator']);
    Route::delete('/goals/{goal}/collaborators/{userId}', [GoalController::class, 'removeCollaborator']);
    Route::put('/goals/{goal}/share', [GoalController::class, 'updateShareSettings']);

    // Quản lý Thùng rác (Trash) của Goals
    Route::get('/goals-trash', [GoalController::class, 'trashed']);
    Route::post('/goals-trash/{goal}/restore', [GoalController::class, 'restore']);
    Route::delete('/goals-trash/{goal}', [GoalController::class, 'forceDelete']);

    // Notes
    Route::apiResource('notes', NoteController::class);
    Route::post('/notes/{note}/goals', [NoteController::class, 'linkGoal']);
    Route::delete('/notes/{note}/goals/{goalId}', [NoteController::class, 'unlinkGoal']);
    Route::post('/notes/{note}/milestones', [NoteController::class, 'linkMilestone']);
    Route::delete('/notes/{note}/milestones/{milestoneId}', [NoteController::class, 'unlinkMilestone']);

    Route::post('/notes/{note}/goals/sync', [NoteController::class, 'syncGoals']);

    Route::prefix('notes-trash')->name('notes.trash.')->group(function () {
        // Lấy danh sách các ghi chú trong thùng rác
        Route::get('/', [NoteController::class, 'trashed'])->name('index');
        // Khôi phục một ghi chú từ thùng rác
        Route::post('/{id}/restore', [NoteController::class, 'restore'])->name('restore');
        Route::post('/{note}/soft-delete', [NoteController::class, 'softDelete'])->name('softDelete');
        Route::delete('/{id}', [NoteController::class, 'forceDeleteFromTrash'])->name('forceDelete');
    });

    // Events
    Route::apiResource('events', EventController::class);
    Route::post('/events/{event}/goals', [EventController::class, 'linkGoal']);
    Route::delete('/events/{event}/goals/{goalId}', [EventController::class, 'unlinkGoal']);

    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    // 1. Route để lấy danh sách các event trong thùng rác
    Route::get('/events-trash', [EventController::class, 'trashed'])->name('events.trashed');
    // 2. Route để khôi phục một event từ thùng rác
    Route::post('/events-trash/{id}/restore', [EventController::class, 'restore'])->name('events.restore');
    // 3. Route để XÓA VĨNH VIỄN một event (dành cho Admin hoặc khi xóa từ thùng rác)
    Route::delete('/events-trash/{id}/force-delete', [EventController::class, 'forceDelete'])->name('events.forceDelete');

    // Milestones
    Route::apiResource('goals.milestones', MilestoneController::class)->shallow();

    Route::get('/goals/{goal}/milestones', [MilestoneController::class, 'index']);
    Route::post('/goals/{goal}/milestones', [MilestoneController::class, 'store']);
    Route::get('/milestones/{milestone}', [MilestoneController::class, 'show']);
    Route::put('/milestones/{milestone}', [MilestoneController::class, 'update']);
    Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy']);

    // Files - Chỉ giữ một bộ routes
    Route::get('/files', [FileController::class, 'index']);
    Route::post('/files', [FileController::class, 'store']);
    Route::get('/files/trash', [FileController::class, 'trashed']); // Di chuyển lên trước {file}
    Route::get('/files/{file}', [FileController::class, 'show']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);
    Route::get('/files/{file}/with-links', [FileController::class, 'showWithLinks']);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    
    // File linking routes
    Route::post('/files/{file}/goals', [FileController::class, 'linkGoal']);
    Route::delete('/files/{file}/goals/{goalId}', [FileController::class, 'unlinkGoal']);
    Route::post('/files/{file}/notes', [FileController::class, 'linkNote']);
    Route::delete('/files/{file}/notes/{noteId}', [FileController::class, 'unlinkNote']);
    
    // File trash routes
    Route::post('/files/{file}/restore', [FileController::class, 'restore']);
    
    // Goals and Notes for linking
    Route::get('/goals', [GoalController::class, 'index']);
    Route::get('/notes', [NoteController::class, 'index']);
});

// =======================================================
// --- PUBLIC CALLBACK ROUTES ---
// =======================================================

Route::any('/payment/vnpay/callback', [PaymentController::class, 'handleVnPayCallback'])->name('payment.vnpay_ipn_callback');