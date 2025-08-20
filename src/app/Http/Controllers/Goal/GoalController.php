<?php

namespace App\Http\Controllers\Goal;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\User;
use App\Models\GoalProgress;
use App\Models\GoalShare;
use App\Models\GoalCollaboration;
use App\Models\Milestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GoalController extends Controller
{
    /**
     * Helper function để load các quan hệ cần thiết và định dạng lại cho frontend.
     * Điều này giúp đảm bảo dữ liệu trả về luôn nhất quán.
     */
    private function loadGoalRelations(Goal $goal)
    {
        // Load các quan hệ từ DB
        $goal->load([
            'milestones',
            'progress',
            'notes',
            'files',
            'events',
            'aiSuggestions',
            'share',
            'collaborations',
            'collaborations.user'
        ]);
    
        // ---- BIẾN ĐỔI DỮ LIỆU ĐỂ PHÙ HỢP VỚI FRONTEND ----
    
        // 1. Chuyển 'share' (object) thành 'shares' (array)
        $goal->shares = $goal->share ? [$goal->share] : [];
        unset($goal->share); // Xóa key 'share' cũ
    
        // 2. Chuyển 'collaborations' thành 'collaborators' và định dạng lại
        $goal->collaborators = $goal->collaborations->map(function ($collab) {
            return [
                'collab_id' => $collab->collab_id,
                'goal_id' => $collab->goal_id,
                'user_id' => $collab->user_id,
                'role' => $collab->role,
                'name' => $collab->user->display_name, // Lấy display_name
                'avatar' => $collab->user->avatar_url, // Lấy avatar_url
            ];
        });
        unset($goal->collaborations); // Xóa key 'collaborations' cũ
    
        return $goal;
    }

    /**
     * Lấy danh sách Goals của người dùng
     */
    public function index()
    {
        $user = Auth::user();
        // Lấy tất cả goals mà user sở hữu
        $goals = Goal::where('user_id', $user->user_id)
            ->with(['milestones', 'progress', 'share', 'collaborations.user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Dùng helper để định dạng lại từng goal
        $formattedGoals = $goals->map(function($goal) {
            return $this->loadGoalRelations($goal);
        });

        // Trả về data dưới dạng key 'data' mà frontend hay dùng
        return response()->json(['data' => $formattedGoals]);
    }


    /**
     * Lưu một Goal mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'sharing_type' => ['required', Rule::in(['private', 'friends', 'public'])],
            'milestones' => 'nullable|array',
            'milestones.*.title' => 'required_with:milestones|string|max:255',
            'milestones.*.deadline' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $goal = DB::transaction(function () use ($request) {
            $goal = Goal::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'new'
            ]);

            // Tạo các record phụ
            GoalProgress::create(['goal_id' => $goal->goal_id, 'progress_value' => 0]);
            GoalShare::create(['goal_id' => $goal->goal_id, 'share_type' => $request->sharing_type]);
            // Tự động thêm owner vào bảng collaboration
            GoalCollaboration::create([
                'goal_id' => $goal->goal_id,
                'user_id' => Auth::id(),
                'role' => 'owner'
            ]);

            if ($request->has('milestones')) {
                foreach ($request->milestones as $milestoneData) {
                    $goal->milestones()->create($milestoneData);
                }
            }

            return $goal;
        });
        
        // Định dạng lại goal trước khi trả về
        $formattedGoal = $this->loadGoalRelations($goal);

        return response()->json([
            'message' => 'Goal created successfully',
            'data' => $formattedGoal
        ], 201);
    }

    /**
     * Lấy chi tiết một Goal
     */
    public function show(Goal $goal)
    {
        // Tạm thời chỉ cho owner, bạn có thể mở rộng logic sau
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Định dạng lại goal trước khi trả về
        $formattedGoal = $this->loadGoalRelations($goal);

        return response()->json(['data' => $formattedGoal]);
    }

    /**
     * Cập nhật thông tin chính của Goal
     */
    public function update(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'status' => ['sometimes', 'required', Rule::in(['new', 'in_progress', 'completed', 'cancelled'])],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Chỉ cập nhật các trường được gửi lên
        $goal->update($validator->validated());
        
        // Định dạng lại goal trước khi trả về
        $formattedGoal = $this->loadGoalRelations($goal->fresh()); // Dùng fresh() để lấy data mới nhất

        return response()->json([
            'message' => 'Goal updated successfully',
            'data' => $formattedGoal
        ]);
    }

    /**
     * --- ENDPOINT CHUYÊN DỤNG ĐỂ CẬP NHẬT SHARING ---
     * Được gọi riêng từ frontend khi người dùng đổi dropdown
     */
    public function updateShareSettings(Request $request, Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'share_type' => ['required', Rule::in(['private', 'friends', 'public'])],
        ]);

        // Cập nhật hoặc tạo mới setting share
        $goal->share()->updateOrCreate(
            ['goal_id' => $goal->goal_id], // Điều kiện tìm kiếm
            ['share_type' => $validated['share_type']] // Dữ liệu để cập nhật/tạo mới
        );

        // Định dạng lại goal trước khi trả về
        $formattedGoal = $this->loadGoalRelations($goal->fresh());

        return response()->json([
            'message' => 'Sharing settings updated successfully!',
            'data' => $formattedGoal
        ]);
    }

    /**
     * Xóa một Goal
     */
    public function destroy(Goal $goal)
    {
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $goal->delete();
        $goal->milestones()->delete();
        return response()->json(['message' => 'Goal moved to trash successfully.'], 204); // 204 No Content là response chuẩn cho delete
    }

    /**
     * [MỚI] - Hiển thị danh sách các goals đã bị xóa mềm (trong thùng rác).
     */
    public function trashed()
    {
        $trashedGoals = Auth::user()->goals()
                               ->onlyTrashed() // Chỉ lấy các mục đã xóa mềm
                               ->orderBy('deleted_at', 'desc')
                               ->paginate(10);

        // Chúng ta không cần load relations phức tạp ở đây vì chỉ cần title và deleted_at
        return response()->json($trashedGoals);
    }

    /**
     * [MỚI] - Khôi phục một goal từ thùng rác.
     * Lưu ý: $goalId được truyền từ URL, không phải Route Model Binding.
     */
    public function restore($goalId)
    {
        $goal = Auth::user()->goals()->onlyTrashed()->findOrFail($goalId);
        
        $goal->restore();

        // [NÂNG CAO] Khôi phục các Milestones của goal đó
        $goal->milestones()->onlyTrashed()->restore(); 

        // Định dạng lại goal đã khôi phục để trả về
        $formattedGoal = $this->loadGoalRelations($goal);

        return response()->json([
            'message' => 'Goal restored successfully',
            'data' => $formattedGoal
        ]);
    }

    /**
     * [MỚI] - Xóa vĩnh viễn một goal ĐÃ NẰM TRONG THÙNG RÁC.
     */
    public function forceDelete($goalId)
    {
        $goal = Auth::user()->goals()->onlyTrashed()->find($goalId);

        if (!$goal) {
            return response()->json(['message' => 'Goal not found in trash'], 404);
        }

        // BẮT BUỘC: Dọn dẹp tất cả các bảng liên quan trước khi xóa vĩnh viễn
        DB::transaction(function () use ($goal) {
            $goal->notes()->detach(); // Xóa liên kết trong bảng goal_note
            $goal->collaborations()->delete(); // Xóa các record cộng tác viên
            $goal->share()->delete(); // Xóa record chia sẻ
            $goal->progress()->delete(); // Xóa record tiến độ
            
            // QUAN TRỌNG: Xóa vĩnh viễn các milestones con
            // Phải dùng vòng lặp nếu milestones có các quan hệ con khác cần xóa
            $goal->milestones()->onlyTrashed()->each(function($milestone) {
                // Giả sử milestone không có quan hệ phức tạp
                $milestone->forceDelete();
            });
            
            // Cuối cùng, xóa vĩnh viễn chính goal đó
            $goal->forceDelete();
        });

        return response()->json(['message' => 'Goal has been permanently deleted.']);

    }

    // Các hàm addCollaborator, removeCollaborator, getAllCollaborators có thể giữ nguyên
    // ...
    public function addCollaborator(Request $request, Goal $goal)
    {
        // 1. Kiểm tra quyền: Chỉ chủ sở hữu goal mới được mời
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized. Only the owner can add collaborators.'], 403);
        }
    
        // 2. Validate dữ liệu gửi lên
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:Users,email',
            'role' => 'sometimes|in:owner,member'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email or user does not exist.', 'errors' => $validator->errors()], 422);
        }
    
        // 3. Tìm người dùng
        $userToAdd = User::where('email', $request->email)->first();
    
        // 4. Các bước kiểm tra logic nghiệp vụ
        if ($userToAdd->user_id === Auth::id()) {
            return response()->json(['message' => 'You cannot add yourself as a collaborator.'], 400);
        }
        
        if ($goal->collaborations()->where('user_id', $userToAdd->user_id)->exists()) {
            return response()->json(['message' => 'This user is already a collaborator.'], 409); // 409 Conflict
        }
    
        // 5. Tạo bản ghi collaboration
        GoalCollaboration::create([
            'goal_id' => $goal->goal_id,
            'user_id' => $userToAdd->user_id,
            'role' => $request->input('role', 'member'),
        ]);
        
        // 6. Định dạng lại goal và trả về
        $formattedGoal = $this->loadGoalRelations($goal->fresh());

        return response()->json([
            'message' => 'Collaborator added successfully',
            'data' => $formattedGoal
        ], 201);
    }

    public function removeCollaborator(Goal $goal, $userId)
    {
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized. Only the owner can remove collaborators.'], 403);
        }

        $collaboration = $goal->collaborations()->where('user_id', $userId)->first();
        
        if (!$collaboration) {
            return response()->json(['message' => 'Collaborator not found.'], 404);
        }
        
        // Không cho xóa owner
        if ($collaboration->role === 'owner') {
             return response()->json(['message' => 'Cannot remove the goal owner.'], 403);
        }
        
        $collaboration->delete();
        
        // Định dạng lại goal và trả về
        $formattedGoal = $this->loadGoalRelations($goal->fresh());
        
        return response()->json([
            'message' => 'Collaborator removed successfully',
            'data' => $formattedGoal
        ]);
    }
}