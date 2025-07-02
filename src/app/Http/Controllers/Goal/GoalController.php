<?php

namespace App\Http\Controllers\Goal;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\GoalProgress;
use App\Models\GoalShare;
use App\Models\GoalCollaboration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->goals();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $goals = $query->with(['milestones', 'progress'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($goals);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'share_type' => 'nullable|in:private,public,friends,collaboration'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $goal = Goal::create([
            'user_id' => Auth::user()->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'new'
        ]);

        // Create initial progress
        GoalProgress::create([
            'goal_id' => $goal->goal_id,
            'progress_value' => 0
        ]);

        // Create share settings if provided
        if ($request->has('share_type')) {
            GoalShare::create([
                'goal_id' => $goal->goal_id,
                'share_type' => $request->share_type
            ]);
        }

        return response()->json([
            'message' => 'Goal created successfully',
            'goal' => $goal->load(['milestones', 'progress', 'share'])
        ], 201);
    }

    public function show(Goal $goal)
    {
        if (!$goal->canBeAccessedBy(Auth::user())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($goal->load([
            'milestones',
            'progress',
            'notes',
            'files',
            'events',
            'aiSuggestions',
            'share',
            'collaborations'
        ]));
    }

    public function update(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'status' => 'in:new,in_progress,completed,cancelled',
            'share_type' => 'nullable|in:private,public,friends,collaboration'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $goal->update($request->only([
            'title',
            'description',
            'start_date',
            'end_date',
            'status'
        ]));

        if ($request->has('share_type')) {
            $goal->share()->updateOrCreate(
                ['goal_id' => $goal->goal_id],
                ['share_type' => $request->share_type]
            );
        }

        return response()->json([
            'message' => 'Goal updated successfully',
            'goal' => $goal->load(['milestones', 'progress', 'share'])
        ]);
    }

    public function destroy(Goal $goal)
    {
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $goal->delete();

        return response()->json([
            'message' => 'Goal deleted successfully'
        ]);
    }

    public function addCollaborator(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:Users,user_id',
            'role' => 'required|in:owner,member'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if user is already a collaborator
        if ($goal->collaborations()->where('user_id', $request->user_id)->exists()) {
            return response()->json([
                'message' => 'User is already a collaborator'
            ], 400);
        }

        $collaboration = GoalCollaboration::create([
            'goal_id' => $goal->goal_id,
            'user_id' => $request->user_id,
            'role' => $request->role
        ]);

        return response()->json([
            'message' => 'Collaborator added successfully',
            'collaboration' => $collaboration
        ]);
    }

    public function removeCollaborator(Goal $goal, $userId)
    {
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $goal->collaborations()->where('user_id', $userId)->delete();

        return response()->json([
            'message' => 'Collaborator removed successfully'
        ]);
    }

    public function updateShareSettings(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'share_type' => 'required|in:private,public,friends,collaboration'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $goal->share()->updateOrCreate(
            ['goal_id' => $goal->goal_id],
            ['share_type' => $request->share_type]
        );

        return response()->json([
            'message' => 'Share settings updated successfully',
            'share' => $goal->share
        ]);
    }
} 