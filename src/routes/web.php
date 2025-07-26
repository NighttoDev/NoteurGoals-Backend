<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Tất cả routes đều thông qua admin domain
Route::get('/', function () {
    // Route mặc định - redirect đến login
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
})->name('admin.home');

// API Routes - include từ api.php
Route::prefix('api')->group(function () {
    // Basic API status routes
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'message' => 'Web server is running'
        ]);
    });

    Route::get('/status', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()
        ]);
    });

    Route::get('/', function () {
        return response()->json([
            'name' => 'NoteurGoals API',
            'version' => '1.0.0'
        ]);
    });

    // Include tất cả API routes từ api.php
    require __DIR__.'/api.php';
});

// Admin Interface Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
});

Route::middleware(['auth', 'admin'])->group(function () {
    // Admin page routes - trả về Inertia pages với data
    Route::get('/dashboard', [AdminController::class, 'showDashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'showUsers'])->name('admin.users');
    Route::get('/goals', [AdminController::class, 'showGoals'])->name('admin.goals');
    Route::get('/notes', [AdminController::class, 'showNotes'])->name('admin.notes');
    Route::get('/events', [AdminController::class, 'showEvents'])->name('admin.events');
    Route::get('/notifications', [AdminController::class, 'showNotifications'])->name('admin.notifications');
    Route::get('/subscriptions', [AdminController::class, 'showSubscriptions'])->name('admin.subscriptions');
    Route::get('/reports', [AdminController::class, 'showReports'])->name('admin.reports');
    Route::get('/settings', [AdminController::class, 'showSettings'])->name('admin.settings');
    
    // Admin actions (POST/DELETE) - cho admin thao tác
    Route::post('/admin/create', [AdminController::class, 'createAdmin'])->name('admin.create-admin');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.delete-user');
    Route::delete('/admin/goals/{id}', [AdminController::class, 'deleteGoal'])->name('admin.delete-goal');
    
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// User authentication routes with prefix to avoid conflicts
// Route::prefix('user')->group(function () {
//     require __DIR__.'/auth.php';
// });
