<?php

namespace App\Http\Controllers\AISuggestion;

use App\Http\Controllers\Controller;
use App\Models\AISuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AISuggestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->aiSuggestions()->orderByDesc('created_at');
        if ($request->has('unread')) {
            $query->where('is_read', false);
        }
        return response()->json($query->paginate(10));
    }

    public function markAsRead(AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $suggestion->is_read = true;
        $suggestion->save();

        return response()->json(['message' => 'Suggestion marked as read']);
    }
}
