<?php

namespace App\Http\Controllers\Milestone;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MilestoneController extends Controller
{
    public function index(Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $milestones = $goal->milestones()->orderBy('deadline')->get();
        return response()->json($milestones);
    }

    public function store(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'deadline' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $milestone = Milestone::create([
            'goal_id' => $goal->id,
            'title' => $request->title,
            'deadline' => $request->deadline,
            'is_completed' => false,
        ]);

        return response()->json(['message' => 'Milestone created', 'milestone' => $milestone], 201);
    }

    public function show(Milestone $milestone)
    {
        if ($milestone->goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($milestone);
    }

    public function update(Request $request, Milestone $milestone)
    {
        if ($milestone->goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:200',
            'deadline' => 'nullable|date',
            'is_completed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $milestone->update($request->only(['title', 'deadline', 'is_completed']));

        return response()->json(['message' => 'Milestone updated', 'milestone' => $milestone]);
    }

    public function destroy(Milestone $milestone)
    {
        if ($milestone->goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $milestone->delete();
        return response()->json(['message' => 'Milestone deleted']);
    }
}
