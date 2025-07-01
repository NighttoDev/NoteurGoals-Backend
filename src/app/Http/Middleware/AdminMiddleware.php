<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();
        
        // Kiểm tra user có trong bảng Admins không
        $admin = \App\Models\Admin::where('user_id', $user->user_id)->first();
        
        if (!$admin) {
            Auth::logout();
            return redirect()->route('admin.login')->withErrors([
                'error' => 'You do not have admin privileges'
            ]);
        }

        return $next($request);
    }
} 