<?php

namespace App\Http\Controllers\Friendship;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FriendshipController extends Controller
{
    /**
     * Lấy danh sách bạn bè và các lời mời đang chờ xử lý.
     * Sửa lại để khớp với CSDL: user_id, display_name, avatar_url
     */
    public function index(Request $request)
    {
        // Auth::id() sẽ tự động lấy khóa chính, nên nó sẽ trả về giá trị của 'user_id'
        $currentUserId = Auth::id(); 

        // 1. Lấy danh sách BẠN BÈ (status = 'accepted')
        $acceptedFriendships = Friendship::where('status', 'accepted')
            ->where(function ($query) use ($currentUserId) {
                $query->where('user_id_1', $currentUserId)
                      ->orWhere('user_id_2', $currentUserId);
            })
            ->with(['user1', 'user2'])
            ->get();
            
        $friends = $acceptedFriendships->map(function ($friendship) use ($currentUserId) {
            $friendUser = $friendship->user_id_1 === $currentUserId ? $friendship->user2 : $friendship->user1;
            return [
                'friendship_id' => $friendship->friendship_id,
                'id' => $friendUser->user_id, // Khóa chính
                'name' => $friendUser->display_name, // Tên hiển thị
                'email' => $friendUser->email,
                'avatar' => $friendUser->avatar_url, // URL ảnh đại diện
            ];
        });

        // 2. Lấy danh sách LỜI MỜI (status = 'pending')
        $pendingFriendships = Friendship::where('status', 'pending')
            ->where(function ($query) use ($currentUserId) {
                $query->where('user_id_1', $currentUserId)
                      ->orWhere('user_id_2', $currentUserId);
            })
            ->with(['user1', 'user2'])
            ->get();

        $requests = $pendingFriendships->map(function ($friendship) use ($currentUserId) {
            if ($friendship->user_id_2 === $currentUserId) {
                $requestUser = $friendship->user1;
                $status = 'received';
            } else {
                $requestUser = $friendship->user2;
                $status = 'sent';
            }
            return [
                'friendship_id' => $friendship->friendship_id,
                'id' => $requestUser->user_id,
                'name' => $requestUser->display_name,
                'email' => $requestUser->email,
                'avatar' => $requestUser->avatar_url,
                'status' => $status,
            ];
        });

        return response()->json([
            'friends' => $friends,
            'requests' => $requests,
        ]);
    }

    /**
     * Gửi lời mời kết bạn bằng user_id.
     */
    public function sendRequestById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:Users,user_id', // Kiểm tra sự tồn tại trong cột 'user_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid user ID.', 'errors' => $validator->errors()], 422);
        }

        $friendId = $request->input('user_id');

        if (Auth::id() == $friendId) {
            return response()->json(['message' => 'You cannot send a friend request to yourself.'], 400);
        }
        
        $existingFriendship = Friendship::where(function($query) use ($friendId) {
            $query->where('user_id_1', Auth::id())->where('user_id_2', $friendId);
        })->orWhere(function($query) use ($friendId) {
            $query->where('user_id_1', $friendId)->where('user_id_2', Auth::id());
        })->first();

        if ($existingFriendship) {
            if ($existingFriendship->status == 'accepted') return response()->json(['message' => 'You are already friends.'], 409);
            if ($existingFriendship->status == 'pending') return response()->json(['message' => 'A friend request is already pending.'], 409);
        }

        $friendship = Friendship::create([
            'user_id_1' => Auth::id(),
            'user_id_2' => $friendId,
        ]);

        return response()->json(['message' => 'Friend request sent', 'friendship' => $friendship], 201);
    }
    
    /**
     * Phản hồi lời mời kết bạn.
     */
    public function respond(Request $request, Friendship $friendship)
    {
        // Auth::user()->user_id sẽ lấy giá trị khóa chính của user đang đăng nhập
        if ($friendship->user_id_2 !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->status === 'rejected') {
            $friendship->delete();
            return response()->json(['message' => 'Friend request rejected']);
        } else {
            $friendship->status = 'accepted';
            $friendship->save();
            return response()->json(['message' => 'Friend request accepted', 'friendship' => $friendship]);
        }
    }

    /**
     * Xóa bạn bè hoặc hủy lời mời.
     */
    public function destroy(Friendship $friendship)
    {
        if ($friendship->user_id_1 !== Auth::user()->user_id && $friendship->user_id_2 !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $friendship->delete();

        return response()->json(['message' => 'Friendship removed successfully']);
    }

    /**
     * Tìm kiếm người dùng theo tên hoặc email.
     * Đã được sửa lại hoàn toàn để khớp CSDL của bạn.
     */
    public function searchUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['users' => []]);
        }

        $searchQuery = $request->input('query');
        $currentUserId = Auth::id(); // Trả về giá trị của cột khóa chính 'user_id'

        // Lấy danh sách ID của những người đã có quan hệ (bạn bè hoặc đang chờ)
        $existingRelations = Friendship::where('user_id_1', $currentUserId)
            ->orWhere('user_id_2', $currentUserId)
            ->get();

        $excludedUserIds = $existingRelations->map(function ($friendship) use ($currentUserId) {
            return $friendship->user_id_1 == $currentUserId ? $friendship->user_id_2 : $friendship->user_id_1;
        })->toArray();
        
        // Thêm cả ID của người dùng hiện tại vào danh sách loại trừ
        $excludedUserIds[] = $currentUserId;

        $users = User::where(function ($query) use ($searchQuery) {
                // Tìm kiếm trong cột 'display_name' và 'email'
                $query->where('display_name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('email', 'LIKE', "%{$searchQuery}%");
            })
            // Loại trừ dựa trên cột khóa chính 'user_id'
            ->whereNotIn('user_id', array_unique($excludedUserIds))
            ->limit(10)
            ->get([
                'user_id',
                'display_name as name',
                'email',
                'avatar_url as avatar'
            ]);

        return response()->json(['users' => $users]);
    }

    // --- CÁC HÀM PLACEHOLDER ---
    public function getCommunityFeed(Request $request)
    {
        return response()->json(['goals' => []]);
    }
    
    public function getUserSuggestions(Request $request)
    {
        return response()->json(['users' => []]);
    }

    // Hàm sendRequest bằng email (có thể không dùng nữa nhưng sửa lại cho đúng)
    public function sendRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:Users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'User with this email does not exist or invalid email.', 'errors' => $validator->errors()], 422);
        }

        $friendUser = User::where('email', $request->email)->first();

        if (Auth::id() == $friendUser->user_id) {
            return response()->json(['message' => 'You cannot send a friend request to yourself.'], 400);
        }
        
        $existingFriendship = Friendship::where(function($query) use ($friendUser) {
            $query->where('user_id_1', Auth::id())->where('user_id_2', $friendUser->user_id);
        })->orWhere(function($query) use ($friendUser) {
            $query->where('user_id_1', $friendUser->user_id)->where('user_id_2', Auth::id());
        })->first();

        if ($existingFriendship) {
            if ($existingFriendship->status == 'accepted') return response()->json(['message' => 'You are already friends.'], 409);
            if ($existingFriendship->status == 'pending') return response()->json(['message' => 'A friend request is already pending.'], 409);
        }

        $friendship = Friendship::create([
            'user_id_1' => Auth::id(),
            'user_id_2' => $friendUser->user_id,
        ]);

        return response()->json(['message' => 'Friend request sent', 'friendship' => $friendship], 201);
    }
}