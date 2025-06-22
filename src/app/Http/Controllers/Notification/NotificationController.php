<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->notifications()->orderByDesc('created_at');
        if ($request->has('unread')) {
            $query->where('is_read', false);
        }
        return response()->json($query->paginate(10));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $notification->is_read = true;
        $notification->save();
        return response()->json(['message' => 'Notification marked as read']);
    }

    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $notification->delete();
        return response()->json(['message' => 'Notification deleted']);
    }
}
