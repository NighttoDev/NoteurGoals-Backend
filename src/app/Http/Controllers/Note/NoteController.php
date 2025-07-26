<?php

namespace App\Http\Controllers\Note;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        // Sử dụng with('goals') để tải kèm các goals đã liên kết
        $notes = Auth::user()->notes()
                    ->with('goals') // <-- THAY ĐỔI QUAN TRỌNG Ở ĐÂY
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                    
        return response()->json($notes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $note = Note::create([
            'user_id' => Auth::user()->user_id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Note created successfully', 'note' => $note], 201);
    }

    public function show(Note $note)
    {
        if ($note->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($note);
    }

    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:200',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $note->update($request->only(['title', 'content']));

        return response()->json(['message' => 'Note updated successfully', 'note' => $note]);
    }

    public function destroy(Note $note)
    {
        if ($note->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $note->delete();
        return response()->json(['message' => 'Note deleted successfully']);
    }

    public function syncGoals(Request $request, Note $note)
    {
        // 1. Kiểm tra quyền sở hữu note
        if ($note->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'goal_ids' => 'present|array', // Phải có key 'goal_ids', có thể là mảng rỗng
            'goal_ids.*' => 'integer|exists:Goals,goal_id', // Mỗi phần tử phải là số và tồn tại trong bảng Goals
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $goalIds = $request->input('goal_ids', []);

        // 3. Kiểm tra quyền sở hữu của tất cả các goal được gửi lên
        $userGoalCount = \App\Models\Goal::where('user_id', Auth::user()->user_id)
                                          ->whereIn('goal_id', $goalIds)
                                          ->count();
        if (count($goalIds) !== $userGoalCount) {
             return response()->json(['message' => 'One or more goals do not belong to the user.'], 403);
        }

        // 4. Sử dụng sync() để đồng bộ hóa
        // sync() sẽ tự động:
        // - Thêm các liên kết mới (attach).
        // - Xóa các liên kết không còn trong mảng `goalIds` (detach).
        // - Giữ nguyên các liên kết đã có.
        $note->goals()->sync($goalIds);
        
        // Lấy lại note với các goals đã được cập nhật để trả về cho frontend
        $note->load('goals');

        return response()->json([
            'message' => 'Goals synced successfully', 
            'note' => $note
        ]);
    }

    public function linkGoal(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'goal_id' => 'required|exists:Goals,goal_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if user owns the goal
        $goal = \App\Models\Goal::find($request->goal_id);
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$note->isLinkedToGoal($request->goal_id)) {
            $note->goals()->attach($request->goal_id);
            return response()->json(['message' => 'Note linked to goal successfully']);
        }

        return response()->json(['message' => 'Note already linked to this goal'], 400);
    }

    public function unlinkGoal(Note $note, $goalId)
    {
        if ($note->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $note->goals()->detach($goalId);
        return response()->json(['message' => 'Note unlinked from goal successfully']);
    }

    public function linkMilestone(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'milestone_id' => 'required|exists:Milestones,milestone_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if user owns the milestone's goal
        $milestone = \App\Models\Milestone::find($request->milestone_id);
        if ($milestone->goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$note->isLinkedToMilestone($request->milestone_id)) {
            $note->milestones()->attach($request->milestone_id);
            return response()->json(['message' => 'Note linked to milestone successfully']);
        }

        return response()->json(['message' => 'Note already linked to this milestone'], 400);
    }

    public function unlinkMilestone(Note $note, $milestoneId)
    {
        if ($note->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $note->milestones()->detach($milestoneId);
        return response()->json(['message' => 'Note unlinked from milestone successfully']);
    }
}
