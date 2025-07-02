<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
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
    Route::post('/notes/{note}/goals', [NoteController::class, 'linkGoal']);
    Route::delete('/notes/{note}/goals/{goalId}', [NoteController::class, 'unlinkGoal']);
    Route::post('/notes/{note}/milestones', [NoteController::class, 'linkMilestone']);
    Route::delete('/notes/{note}/milestones/{milestoneId}', [NoteController::class, 'unlinkMilestone']);

    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
    Route::post('/events/{event}/goals', [EventController::class, 'linkGoal']);
    Route::delete('/events/{event}/goals/{goalId}', [EventController::class, 'unlinkGoal']);

    // Milestones
    Route::get('/goals/{goal}/milestones', [MilestoneController::class, 'index']);
    Route::post('/goals/{goal}/milestones', [MilestoneController::class, 'store']);
    Route::get('/milestones/{milestone}', [MilestoneController::class, 'show']);
    Route::put('/milestones/{milestone}', [MilestoneController::class, 'update']);
    Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    // Friendships
    Route::get('/friends', [FriendshipController::class, 'index']);
    Route::post('/friends/request', [FriendshipController::class, 'sendRequest']);
    Route::post('/friends/{friendship}/respond', [FriendshipController::class, 'respond']);
    Route::delete('/friends/{friendship}', [FriendshipController::class, 'destroy']);

    // Files
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

    // AI Suggestions
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

    // Subscriptions
    Route::get('/subscription-plans', [SubscriptionController::class, 'plans']);
    Route::get('/my-subscriptions', [SubscriptionController::class, 'mySubscriptions']);
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
});

// Thêm routes cho callbacks trực tiếp không phụ thuộc session
Route::get('/auth/google/callback-direct', [AuthController::class, 'handleGoogleCallbackDirect']);
Route::get('/auth/facebook/callback-direct', [AuthController::class, 'handleFacebookCallbackDirect']);

// Thêm các routes mới cho xác thực email
Route::get('/auth/verify/{id}/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerificationEmail']);
