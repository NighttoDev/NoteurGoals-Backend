<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $admin = Admin::where('user_id', $user->id)->first();
        
        if (!$admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
} 