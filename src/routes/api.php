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


// Goal management
    Route::get('/goals', [GoalController::class, 'index']);
    Route::post('/goals', [GoalController::class, 'store']);
    
    // [SỬA ĐỔI] Đưa route cụ thể này lên trước
    Route::get('/goals/trash', [GoalController::class, 'trashed'])->name('goals.trashed');
    
    // Các route có tham số {goal} nằm bên dưới
Route::get('/goals', [GoalController::class, 'index']);
    Route::post('/goals', [GoalController::class, 'store']);
    Route::get('/goals/{goal}', [GoalController::class, 'show']);
    Route::put('/goals/{goal}', [GoalController::class, 'update']);
    
    // [XÓA MỀM] - Route này bây giờ sẽ chuyển goal vào thùng rác
    Route::delete('/goals/{goal}', [GoalController::class, 'destroy']);
    
    // Các route cho nghiệp vụ phụ
    Route::post('/goals/{goal}/collaborators', [GoalController::class, 'addCollaborator']);
    Route::delete('/goals/{goal}/collaborators/{userId}', [GoalController::class, 'removeCollaborator']);
    Route::put('/goals/{goal}/share', [GoalController::class, 'updateShareSettings']);

    // --- [MỚI] Các route cho Thùng rác (Trash) của Goals ---
    // URL được đổi thành 'goals-trash' để khớp với frontend
    Route::get('/goals-trash', [GoalController::class, 'trashed'])->name('goals.trashed');
    Route::post('/goals-trash/{goal}/restore', [GoalController::class, 'restore'])->name('goals.restore');
    Route::delete('/goals-trash/{goal}', [GoalController::class, 'forceDelete'])->name('goals.forceDelete');

    // Notes
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/notes/{note}', [NoteController::class, 'show']);
    Route::put('/notes/{note}', [NoteController::class, 'update']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);
    Route::post('/notes/{note}/goals/sync', [NoteController::class, 'syncGoals']);

     // 1. Route để XÓA MỀM (chuyển note vào thùng rác)
    Route::post('/notes/{note}/soft-delete', [NoteController::class, 'softDelete'])->name('notes.softDelete');

    // 2. Các route để quản lý thùng rác
    Route::prefix('notes-trash')->name('notes.trash.')->group(function () {
        // Lấy danh sách các ghi chú trong thùng rác
        Route::get('/', [NoteController::class, 'trashed'])->name('index');

        // Khôi phục một ghi chú từ thùng rác
        Route::post('/{id}/restore', [NoteController::class, 'restore'])->name('restore');

        // Xóa vĩnh viễn một ghi chú khỏi thùng rác
        Route::delete('/{id}', [NoteController::class, 'forceDeleteFromTrash'])->name('forceDelete');
    });

    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    
    // Route này giờ đã thực hiện chức năng XÓA MỀM
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    // --- CÁC ROUTE MỚI CHO THÙNG RÁC CỦA EVENT (Bổ sung) ---

    // 1. Route để lấy danh sách các event trong thùng rác
    Route::get('/events-trash', [EventController::class, 'trashed'])->name('events.trashed');

    // 2. Route để khôi phục một event từ thùng rác
    Route::post('/events-trash/{id}/restore', [EventController::class, 'restore'])->name('events.restore');
    
    // 3. Route để XÓA VĨNH VIỄN một event (dành cho Admin hoặc khi xóa từ thùng rác)
    Route::delete('/events-trash/{id}/force-delete', [EventController::class, 'forceDelete'])->name('events.forceDelete');

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

// Route IPN vẫn giữ lại để dùng sau này.
Route::any('/payment/vnpay/callback', [PaymentController::class, 'handleVnPayCallback'])->name('payment.vnpay_ipn_callback');
