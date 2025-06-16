<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Health check cho root domain (admin.noteurgoals.live)
// Route::get('/health', function () {
//     return response()->json([
//         'status' => 'healthy',
//         'service' => 'NoteurGoals Backend',
//         'timestamp' => now()
//     ]);
// });

// Route::get('/', function () {
//     return response()->json([
//         'message' => 'NoteurGoals Backend API',
//         'status' => 'running',
//         'version' => '1.0.0',
//         'endpoints' => [
//             'api' => url('/api'),
//             'health' => url('/health'),
//             'docs' => url('/api/documentation')
//         ]
//     ]);
// });
Route::get('/', function () {
    return view('app');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
