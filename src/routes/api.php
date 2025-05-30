<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Goal\GoalController;
use App\Http\Controllers\Note\NoteController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\File\FileController;
use App\Http\Controllers\Admin\AdminController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::post('/collaborators', [AdminController::class, 'createCollaborator']);
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/goals', [AdminController::class, 'goals']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::delete('/goals/{id}', [AdminController::class, 'deleteGoal']);
    });

    // Goal routes
    Route::apiResource('goals', GoalController::class);
    Route::post('goals/{goal}/collaborators', [GoalController::class, 'addCollaborator']);
    Route::delete('goals/{goal}/collaborators/{userId}', [GoalController::class, 'removeCollaborator']);
    Route::patch('goals/{goal}/share', [GoalController::class, 'updateShareSettings']);

    // Note routes
    Route::apiResource('notes', NoteController::class);
    Route::post('notes/{note}/goals/{goal}', [NoteController::class, 'attachToGoal']);
    Route::delete('notes/{note}/goals/{goal}', [NoteController::class, 'detachFromGoal']);
    Route::post('notes/{note}/milestones/{milestone}', [NoteController::class, 'attachToMilestone']);
    Route::delete('notes/{note}/milestones/{milestone}', [NoteController::class, 'detachFromMilestone']);

    // Event routes
    Route::apiResource('events', EventController::class);
    Route::post('events/{event}/goals/{goal}', [EventController::class, 'attachToGoal']);
    Route::delete('events/{event}/goals/{goal}', [EventController::class, 'detachFromGoal']);
    Route::post('events/{event}/recurring', [EventController::class, 'setRecurring']);

    // File routes
    Route::apiResource('files', FileController::class);
    Route::post('files/{file}/goals/{goal}', [FileController::class, 'attachToGoal']);
    Route::delete('files/{file}/goals/{goal}', [FileController::class, 'detachFromGoal']);
    Route::post('files/{file}/notes/{note}', [FileController::class, 'attachToNote']);
    Route::delete('files/{file}/notes/{note}', [FileController::class, 'detachFromNote']);
}); 