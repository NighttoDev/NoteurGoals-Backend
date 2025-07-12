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
     * Dữ liệu cộng tác viên sẽ được lấy qua GoalController.
     */
    public function index(Request $request)
    {
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
                'id' => $friendUser->user_id,
                'display_name' => $friendUser->display_name,
                'email' => $friendUser->email,
                'avatar' => $friendUser->avatar,
            ];
        });

        // 2. Lấy danh sách LỜI MỜI (status = 'pending')
        $pendingFriendships = Friendship::where('status', 'pending')
            ->where(function ($query) use ($currentUserId) {
                $query->where('user_id_1', $currentUserId) // Lời mời đã gửi
                      ->orWhere('user_id_2', $currentUserId); // Lời mời đã nhận
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
                'display_name' => $requestUser->display_name,
                'email' => $requestUser->email,
                'avatar' => $requestUser->avatar,
                'status' => $status,
            ];
        });

        // 3. Trả về JSON với cấu trúc mà frontend mong đợi từ API /friends
        return response()->json([
            'friends' => $friends,
            'requests' => $requests,
        ]);
    }

    /**
     * Gửi lời mời kết bạn bằng email.
     */
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
            if ($existingFriendship->status == 'accepted') {
                return response()->json(['message' => 'You are already friends.'], 409);
            }
             if ($existingFriendship->status == 'pending') {
                return response()->json(['message' => 'A friend request is already pending.'], 409);
            }
        }

        $friendship = Friendship::create([
            'user_id_1' => Auth::id(),
            'user_id_2' => $friendUser->user_id,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Friend request sent', 'friendship' => $friendship], 201);
    }

    /**
     * Phản hồi lời mời kết bạn.
     */
    public function respond(Request $request, Friendship $friendship)
    {
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
}