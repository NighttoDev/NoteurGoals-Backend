<?php

use App\Http\Controllers\ProfileController;
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
