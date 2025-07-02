<?php

namespace App\Http\Controllers\AISuggestion;

use App\Http\Controllers\Controller;
use App\Models\AISuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AISuggestionController extends Controller
{
    public function index(Request $request)
    {
        $suggestions = Auth::user()->AISuggestions()
            ->when($request->has('is_read'), function ($query) use ($request) {
                $query->where('is_read', $request->is_read);
            })
            ->when($request->has('suggestion_type'), function ($query) use ($request) {
                $query->where('suggestion_type', $request->suggestion_type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($suggestions);
    }

    public function show(AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($suggestion->load('goals'));
    }

    public function markAsRead(AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $suggestion->markAsRead();

        return response()->json(['message' => 'Suggestion marked as read']);
    }

    public function markAsUnread(AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $suggestion->markAsUnread();

        return response()->json(['message' => 'Suggestion marked as unread']);
    }

    // Goal linking methods
    public function linkGoal(Request $request, AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'goal_id' => 'required|exists:Goals,goal_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $goal = \App\Models\Goal::find($request->goal_id);
        
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($suggestion->linkGoal($request->goal_id)) {
            return response()->json(['message' => 'Goal linked to suggestion successfully']);
        } else {
            return response()->json(['message' => 'Goal is already linked to this suggestion'], 409);
        }
    }

    public function unlinkGoal(AISuggestion $suggestion, $goalId)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($suggestion->unlinkGoal($goalId)) {
            return response()->json(['message' => 'Goal unlinked from suggestion successfully']);
        } else {
            return response()->json(['message' => 'Goal was not linked to this suggestion'], 404);
        }
    }

    // Get suggestion with all its links
    public function showWithLinks(AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($suggestion->load('goals'));
    }

    // Mark all suggestions as read
    public function markAllAsRead()
    {
        Auth::user()->AISuggestions()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All suggestions marked as read']);
    }

    // Get unread count
    public function getUnreadCount()
    {
        $count = Auth::user()->AISuggestions()
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    // Get suggestions by type
    public function getByType(Request $request, $type)
    {
        $validTypes = ['goal_breakdown', 'priority', 'completion_forecast'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['message' => 'Invalid suggestion type'], 400);
        }

        $suggestions = Auth::user()->AISuggestions()
            ->byType($type)
            ->when($request->has('is_read'), function ($query) use ($request) {
                $query->where('is_read', $request->is_read);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($suggestions);
    }
}
