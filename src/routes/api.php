<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Goal\GoalController;
use App\Http\Controllers\Note\NoteController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\File\FileController;
use App\Http\Controllers\Admin\AdminController;

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

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Notification\NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Notification\NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\Notification\NotificationController::class, 'destroy']);

    // Friendships
    Route::get('/friends', [\App\Http\Controllers\Friendship\FriendshipController::class, 'index']);
    Route::post('/friends/request', [\App\Http\Controllers\Friendship\FriendshipController::class, 'sendRequest']);
    Route::post('/friends/{friendship}/respond', [\App\Http\Controllers\Friendship\FriendshipController::class, 'respond']);
    Route::delete('/friends/{friendship}', [\App\Http\Controllers\Friendship\FriendshipController::class, 'destroy']);

    // Files
    Route::get('/files', [\App\Http\Controllers\File\FileController::class, 'index']);
    Route::post('/files', [\App\Http\Controllers\File\FileController::class, 'store']);
    Route::get('/files/{file}', [\App\Http\Controllers\File\FileController::class, 'show']);
    Route::delete('/files/{file}', [\App\Http\Controllers\File\FileController::class, 'destroy']);

    // AI Suggestions
    Route::get('/ai-suggestions', [\App\Http\Controllers\AISuggestion\AISuggestionController::class, 'index']);
    Route::post('/ai-suggestions/{suggestion}/read', [\App\Http\Controllers\AISuggestion\AISuggestionController::class, 'markAsRead']);

    // Subscriptions
    Route::get('/subscription-plans', [\App\Http\Controllers\Subscription\SubscriptionController::class, 'plans']);
    Route::get('/my-subscriptions', [\App\Http\Controllers\Subscription\SubscriptionController::class, 'mySubscriptions']);
    Route::post('/subscribe', [\App\Http\Controllers\Subscription\SubscriptionController::class, 'subscribe']);
    Route::post('/subscriptions/{subscription}/cancel', [\App\Http\Controllers\Subscription\SubscriptionController::class, 'cancel']);
});

// Thêm routes cho callbacks trực tiếp không phụ thuộc session
Route::get('/auth/google/callback-direct', [AuthController::class, 'handleGoogleCallbackDirect']);
Route::get('/auth/facebook/callback-direct', [AuthController::class, 'handleFacebookCallbackDirect']);

// Thêm các routes mới cho xác thực email
Route::get('/auth/verify/{id}/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerificationEmail']);
