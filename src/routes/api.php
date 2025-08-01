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
// Giả sử bạn tạo Controller này để quản lý các tính năng cộng đồng
use App\Http\Controllers\Community\CommunityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =======================================================
// --- PUBLIC & AUTHENTICATION ROUTES ---
// =======================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/auth/google/callback-direct', [AuthController::class, 'handleGoogleCallbackDirect']);
Route::get('/auth/facebook/callback-direct', [AuthController::class, 'handleFacebookCallbackDirect']);

// =======================================================
// --- PROTECTED ROUTES (Require sanctum authentication) ---
// =======================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Core Auth & User Info ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // --- User Profile & Account Management ---
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::post('/profile/update', [UserController::class, 'updateProfile']);
        Route::post('/password/change', [UserController::class, 'changePassword']);
        Route::post('/account/delete', [UserController::class, 'deleteAccount']);
        // Route cho chức năng báo cáo người dùng
        Route::post('/{user}/report', [UserController::class, 'reportUser']);
    });

    // --- Friendships & Community ---
    Route::get('/friends', [FriendshipController::class, 'index']);
    // Route này sẽ chấp nhận cả email và user_id
    Route::post('/friends/request', [FriendshipController::class, 'sendRequest']);
    Route::post('/friends/{friendship}/respond', [FriendshipController::class, 'respond']);
    Route::delete('/friends/{friendship}', [FriendshipController::class, 'destroy']);
    
    // CÁC ROUTE MỚI
    Route::get('/users/suggestions', [FriendshipController::class, 'getSuggestions']);
    Route::get('/users/search', [FriendshipController::class, 'searchUsers']);
    Route::get('/community/feed', [CommunityController::class, 'getFeed']);

    // --- Goal management ---
    Route::apiResource('goals', GoalController::class);
    Route::post('/goals/{goal}/collaborators', [GoalController::class, 'addCollaborator']);
    Route::delete('/goals/{goal}/collaborators/{userId}', [GoalController::class, 'removeCollaborator']);
    Route::put('/goals/{goal}/share', [GoalController::class, 'updateShareSettings']);

    // --- Notes ---
    Route::apiResource('notes', NoteController::class)->except(['create', 'edit']);
    Route::post('/notes/{note}/goals/sync', [NoteController::class, 'syncGoals']);
    Route::post('/notes/{note}/milestones', [NoteController::class, 'linkMilestone']);
    Route::delete('/notes/{note}/milestones/{milestoneId}', [NoteController::class, 'unlinkMilestone']);

    // --- Events ---
    Route::apiResource('events', EventController::class)->except(['create', 'edit']);
    Route::post('/events/{event}/goals', [EventController::class, 'linkGoal']);
    Route::delete('/events/{event}/goals/{goalId}', [EventController::class, 'unlinkGoal']);

    // --- Milestones ---
    Route::get('/goals/{goal}/milestones', [MilestoneController::class, 'index']);
    Route::post('/goals/{goal}/milestones', [MilestoneController::class, 'store']);
    Route::get('/milestones/{milestone}', [MilestoneController::class, 'show']);
    Route::put('/milestones/{milestone}', [MilestoneController::class, 'update']);
    Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy']);

    // --- Notifications ---
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    // --- Friendships ---
    Route::get('/friends', [FriendshipController::class, 'index']);
    Route::post('/friends/request', [FriendshipController::class, 'sendRequest']);
    Route::post('/friends/{friendship}/respond', [FriendshipController::class, 'respond']);
    Route::delete('/friends/{friendship}', [FriendshipController::class, 'destroy']);
    // Route này để lấy danh sách cộng tác viên từ các mục tiêu
    Route::get('/collaborators', [GoalController::class, 'getAllCollaborators']);

    // --- Files ---
    Route::get('/files', [FileController::class, 'index']);
    Route::post('/files', [FileController::class, 'store']);
    Route::get('/files/{file}', [FileController::class, 'show']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);
    Route::get('/files/{file}/with-links', [FileController::class, 'showWithLinks']);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::post('/files/{file}/goals', [FileController::class, 'linkGoal']);
    Route::delete('/files/{file}/goals/{goalId}', [FileController::class, 'unlinkGoal']);
    Route::post('/files/{file}/notes', [FileController::class, 'linkNote']);
    Route::delete('/files/{file}/notes/{noteId}', [FileController::class, 'unlinkNote']);

    // --- AI Suggestions ---
    Route::get('/ai-suggestions', [AISuggestionController::class, 'index']);
    Route::get('/ai-suggestions/{suggestion}', [AISuggestionController::class, 'show']);
    Route::get('/ai-suggestions/{suggestion}/with-links', [AISuggestionController::class, 'showWithLinks']);
    Route::get('/ai-suggestions/type/{type}', [AISuggestionController::class, 'getByType']);
    Route::get('/ai-suggestions/unread/count', [AISuggestionController::class, 'getUnreadCount']);
    Route::post('/ai-suggestions/{suggestion}/read', [AISuggestionController::class, 'markAsRead']);
    Route::post('/ai-suggestions/{suggestion}/unread', [AISuggestionController::class, 'markAsUnread']);
    Route::post('/ai-suggestions/read-all', [AISuggestionController::class, 'markAllAsRead']);
    Route::post('/ai-suggestions/{suggestion}/goals', [AISuggestionController::class, 'linkGoal']);
    Route::delete('/ai-suggestions/{suggestion}/goals/{goalId}', [AISuggestionController::class, 'unlinkGoal']);

    // --- Subscriptions ---
    Route::get('/subscription-plans', [SubscriptionController::class, 'plans']);
    Route::get('/my-subscriptions', [SubscriptionController::class, 'mySubscriptions']);
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
});

// Ghi chú: Các route bị comment lại hoặc không dùng đến đã được lược bỏ để giữ sự gọn gàng.
// Route::get('/user', ...); đã được thay thế bằng /me hoặc /user/profile để tường minh hơn.
// Route::get('/auth/verify/{id}/{token}', ...); đã được thay thế bằng luồng OTP với POST /verify-email.