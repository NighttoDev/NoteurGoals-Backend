<?php

namespace App\Http\Controllers\Goal;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\User;
use App\Models\GoalProgress;
use App\Models\GoalShare;
use App\Models\GoalCollaboration;
use App\Models\Milestone; // <-- Đã import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- Đã import
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->goals();

        // Filter by status, bỏ qua nếu status là 'all'
        if ($request->has('status') && $request->status !== 'all') {
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

        // Eager load các relationship để tối ưu query
        $goals = $query->with(['milestones', 'progress', 'share', 'collaborations'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($goals);
    }

    public function store(Request $request)
    {
        // Thêm validation cho milestones và share_type
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'share_type' => 'nullable|in:private,public,friends,collaboration',
            'milestones' => 'nullable|array',
            'milestones.*.title' => 'required_with:milestones|string|max:255',
            'milestones.*.deadline' => 'required_with:milestones|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Sử dụng DB Transaction để đảm bảo toàn vẹn dữ liệu
        $goal = DB::transaction(function () use ($request) {
            $goal = Goal::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'new'
            ]);

            GoalProgress::create(['goal_id' => $goal->goal_id, 'progress_value' => 0]);

            if ($request->filled('share_type')) {
                GoalShare::create(['goal_id' => $goal->goal_id, 'share_type' => $request->share_type]);
            }

            // Logic để tạo các milestones
            if ($request->has('milestones')) {
                $milestonesData = collect($request->milestones)->map(fn($m) => new Milestone($m));
                $goal->milestones()->saveMany($milestonesData);
            }

            return $goal;
        });

        return response()->json([
            'message' => 'Goal created successfully',
            'goal' => $goal->load(['milestones', 'progress', 'share'])
        ], 201);
    }

    public function show(Goal $goal)
    {
        // Tận dụng phương thức helper trong model để kiểm tra quyền
        if (!$goal->canBeAccessedBy(Auth::user())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($goal->load([
            'milestones', 'progress', 'notes', 'files', 'events',
            'aiSuggestions', 'share', 'collaborations'
        ]));
    }

    public function update(Request $request, Goal $goal)
    {
        // Chỉ chủ sở hữu mới có quyền cập nhật toàn bộ
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'status' => 'sometimes|in:new,in_progress,completed,cancelled',
            'share_type' => 'nullable|in:private,public,friends,collaboration',
            'milestones' => 'nullable|array',
            'milestones.*.title' => 'required_with:milestones|string|max:255',
            'milestones.*.deadline' => 'required_with:milestones|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request, $goal) {
            // Cập nhật các trường chính của Goal
            $goal->update($request->except(['milestones', 'share_type']));

            // Cập nhật hoặc tạo mới cài đặt chia sẻ
            if ($request->has('share_type')) {
                $goal->share()->updateOrCreate([], ['share_type' => $request->share_type]);
            }

            // Đồng bộ hóa milestones (xóa cũ, tạo mới)
            if ($request->has('milestones')) {
                $goal->milestones()->delete();
                if (!empty($request->milestones)) {
                    $milestonesData = collect($request->milestones)->map(fn($m) => new Milestone($m));
                    $goal->milestones()->saveMany($milestonesData);
                }
            }
            
            // Tự động cập nhật lại status dựa trên progress nếu status không được gửi lên thủ công
            if (!$request->has('status')) {
                $goal->refresh()->updateStatus();
            }
        });

        // Dùng fresh() để lấy lại dữ liệu mới nhất từ DB
        return response()->json([
            'message' => 'Goal updated successfully',
            'goal' => $goal->fresh()->load(['milestones', 'progress', 'share'])
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
        // Chỉ chủ sở hữu mới được xóa
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized. Only the owner can delete the goal.'], 403);
        }

        // Vì đã có trait SoftDeletes trong Model, lệnh này sẽ là XÓA MỀM
        $goal->delete();

        // [Nâng cao] Xóa mềm các Milestones liên quan để chúng cũng vào thùng rác
        $goal->milestones()->delete();

        return response()->json(['message' => 'Goal moved to trash successfully.'], 200);
    }

    // ... các hàm addCollaborator, removeCollaborator giữ nguyên ...

    // ===================================================================
    // CÁC HÀM MỚI ĐƯỢC THÊM ĐỂ QUẢN LÝ THÙNG RÁC
    // ===================================================================

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
    
    $goal->restore(); // <--- ĐÃ CHẠY THÀNH CÔNG

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

    public function addCollaborator(Request $request, Goal $goal)
    {
        // 1. Kiểm tra quyền: Chỉ chủ sở hữu goal mới được mời
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized. Only the owner can add collaborators.'], 403);
        }
    
        // 2. Validate dữ liệu gửi lên: Yêu cầu email và email đó phải tồn tại
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:Users,email',
            'role' => 'sometimes|in:owner,member'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email or user does not exist.', 'errors' => $validator->errors()], 422);
        }
    
        // 3. Tìm người dùng dựa trên email
        $userToAdd = User::where('email', $request->email)->first();
    
        // 4. Các bước kiểm tra logic nghiệp vụ
        if ($userToAdd->user_id === Auth::id()) {
            return response()->json(['message' => 'You cannot add yourself as a collaborator.'], 400);
        }
        
        if ($goal->collaborations()->where('user_id', $userToAdd->user_id)->exists()) {
            return response()->json(['message' => 'This user is already a collaborator.'], 409); // 409 Conflict
        }
    
        // 5. Tạo bản ghi collaboration mới
        $collaboration = GoalCollaboration::create([
            'goal_id' => $goal->goal_id,
            'user_id' => $userToAdd->user_id,
            'role' => $request->input('role', 'member'),
            // Tự động điền 'joined_at' nếu model của bạn yêu cầu
            // 'joined_at' => now() // Bỏ comment dòng này nếu cần
        ]);
    
        // 6. Trả về response thành công kèm theo dữ liệu collaboration mới
        // .load('user') để đảm bảo thông tin của user (tên, avatar...) được gửi về cho frontend
        return response()->json([
            'message' => 'Collaborator added successfully',
            'collaboration' => $collaboration->load('user')
        ], 201);
    }

    public function removeCollaborator(Goal $goal, $userId)
    {
        // Chỉ chủ sở hữu mới được xóa cộng tác viên
        if ($goal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized. Only the owner can remove collaborators.'], 403);
        }

        $goal->collaborations()->where('user_id', $userId)->delete();

        return response()->json(['message' => 'Collaborator removed successfully']);
    }

    public function getAllCollaborators()
    {
        $currentUserId = Auth::id();

        // Lấy ID của tất cả các mục tiêu mà người dùng hiện tại là chủ sở hữu.
        $ownedGoalIds = Goal::where('user_id', $currentUserId)->pluck('goal_id');

        if ($ownedGoalIds->isEmpty()) {
            return response()->json([]);
        }
        
        // Lấy ID của tất cả người dùng cộng tác trên các mục tiêu đó (trừ chính chủ sở hữu).
        $collaboratorIds = GoalCollaboration::whereIn('goal_id', $ownedGoalIds)
            ->where('user_id', '!=', $currentUserId)
            ->distinct()
            ->pluck('user_id');

        if ($collaboratorIds->isEmpty()) {
            return response()->json([]);
        }

        // Lấy thông tin chi tiết của các cộng tác viên.
        // Dùng alias 'id' để frontend dễ dàng xử lý (key={collaborator.id})
        $collaborators = User::whereIn('user_id', $collaboratorIds)
            ->select('user_id as id', 'display_name', 'email', 'avatar')
            ->get();

        return response()->json($collaborators);
    }
}