<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Health check cho root domain (admin.noteurgoals.live)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Web server is running'
    ]);
});

Route::get('/', function () {
    return response()->json([
        'name' => 'NoteurGoals API',
        'version' => '1.0.0'
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/status', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()
    ]);
});

require __DIR__.'/auth.php';

// Admin Routes - served from dedicated admin domain
$adminDomain = config('app.admin_domain');
Route::domain($adminDomain)->prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::inertia('/dashboard', 'Admin/Dashboard')->name('admin.dashboard');
        Route::inertia('/users', 'Admin/Users')->name('admin.users');
        Route::inertia('/goals', 'Admin/Goals')->name('admin.goals');
        Route::inertia('/notes', 'Admin/Notes')->name('admin.notes');
        Route::inertia('/events', 'Admin/Events')->name('admin.events');
        Route::inertia('/milestones', 'Admin/Milestones')->name('admin.milestones');
        Route::inertia('/notifications', 'Admin/Notifications')->name('admin.notifications');
        Route::inertia('/files', 'Admin/Files')->name('admin.files');
        Route::inertia('/ai-suggestions', 'Admin/AISuggestions')->name('admin.ai');
        Route::inertia('/subscriptions', 'Admin/Subscriptions')->name('admin.subscriptions');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    });
});
