<?php

namespace App\Http\Controllers\Goal;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Milestone;
use App\Models\GoalProgress;
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
        return response()->json($goal->milestones);
    }

    public function store(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'deadline' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $milestone = $goal->milestones()->create([
            'title' => $request->title,
            'deadline' => $request->deadline,
            'is_completed' => false
        ]);

        $this->updateGoalProgress($goal);

        return response()->json(['milestone' => $milestone], 201);
    }

    public function update(Request $request, Goal $goal, Milestone $milestone)
    {
        if ($goal->user_id !== Auth::id() || $milestone->goal_id !== $goal->goal_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:200',
            'deadline' => 'nullable|date',
            'is_completed' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $milestone->update($request->only(['title','deadline','is_completed']));
        $this->updateGoalProgress($goal);

        return response()->json(['milestone' => $milestone]);
    }

    public function destroy(Goal $goal, Milestone $milestone)
    {
        if ($goal->user_id !== Auth::id() || $milestone->goal_id !== $goal->goal_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $milestone->delete();
        $this->updateGoalProgress($goal);
        return response()->json(['message' => 'Milestone deleted']);
    }

    protected function updateGoalProgress(Goal $goal): void
    {
        $progressValue = $goal->calculateProgress();
        GoalProgress::updateOrCreate(
            ['goal_id' => $goal->goal_id],
            ['progress_value' => $progressValue]
        );
        $goal->updateStatus();
    }
}
