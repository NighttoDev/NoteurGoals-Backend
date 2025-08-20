<?php

namespace App\Http\Controllers\Friendship;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use App\Models\GoalCollaboration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FriendshipController extends Controller
{
    /**
     * Lấy danh sách bạn bè và các lời mời đang chờ xử lý.
     */
    public function index(Request $request)
    {
        $currentUserId = Auth::id();

        $acceptedFriendships = Friendship::where('status', 'accepted')
            ->where(function ($query) use ($currentUserId) {
                $query->where('user_id_1', $currentUserId)
                      ->orWhere('user_id_2', $currentUserId);
            })
            ->get();
            
        // Collect friend user ids (may be empty)
        $friendIds = $acceptedFriendships->map(function ($friendship) use ($currentUserId) {
            return $friendship->user_id_1 === $currentUserId ? $friendship->user_id_2 : $friendship->user_id_1;
        })->unique()->values();
        // Fetch users with counts and profile in one go to avoid N+1
        $usersById = User::whereIn('user_id', $friendIds)
            ->with(['profile'])
            ->withCount(['goals', 'notes'])
            ->get()
            ->keyBy('user_id');

        // Build friends payload with counts
        $friends = $acceptedFriendships->map(function ($friendship) use ($currentUserId, $usersById) {
            $friendId = $friendship->user_id_1 === $currentUserId ? $friendship->user_id_2 : $friendship->user_id_1;
            $u = $usersById->get($friendId);
            return [
                'friendship_id' => $friendship->friendship_id,
                'id' => $u?->user_id,
                'name' => $u?->display_name,
                'email' => $u?->email,
                'avatar' => $u?->avatar_url,
                'total_goals' => (int)($u?->goals_count ?? 0),
                'total_notes' => (int)($u?->notes_count ?? 0),
                'is_premium' => (bool)optional($u?->profile)->is_premium,
            ];
        });

        $pendingFriendships = Friendship::where('status', 'pending')
            ->where(function ($query) use ($currentUserId) {
                $query->where('user_id_1', $currentUserId)
                      ->orWhere('user_id_2', $currentUserId);
            })
            ->with(['user1', 'user2'])
            ->get();

        $requests = $pendingFriendships->map(function ($friendship) use ($currentUserId) {
            $requestUser = $friendship->user_id_1 === $currentUserId ? $friendship->user2 : $friendship->user1;
            $status = $friendship->user_id_1 === $currentUserId ? 'sent' : 'received';
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
            'user_id' => 'required|integer|exists:Users,user_id',
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
        $currentUserId = Auth::id();

        $existingRelations = Friendship::where('user_id_1', $currentUserId)
            ->orWhere('user_id_2', $currentUserId)
            ->get();

        $excludedUserIds = $existingRelations->map(function ($friendship) use ($currentUserId) {
            return $friendship->user_id_1 == $currentUserId ? $friendship->user_id_2 : $friendship->user_id_1;
        })->toArray();
        
        $excludedUserIds[] = $currentUserId;

        $users = User::where(function ($query) use ($searchQuery) {
                $query->where('display_name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('email', 'LIKE', "%{$searchQuery}%");
            })
            ->whereNotIn('user_id', array_unique($excludedUserIds))
            ->select(['user_id', 'display_name', 'email', 'avatar_url'])
            ->with(['profile'])
            ->withCount(['goals', 'notes'])
            ->limit(10)
            ->get();

        // Enrich with friend status and unified keys used by frontend
        $existingByPair = $existingRelations->keyBy(function ($f) use ($currentUserId) {
            // key by the other user's id for quick lookup
            return $f->user_id_1 == $currentUserId ? $f->user_id_2 : $f->user_id_1;
        });

        $result = $users->map(function ($u) use ($existingByPair, $currentUserId) {
            $friendStatus = 'not_friends';
            $friendship = $existingByPair->get($u->user_id);
            if ($friendship) {
                if ($friendship->status === 'accepted') {
                    $friendStatus = 'friends';
                } elseif ($friendship->status === 'pending') {
                    $friendStatus = $friendship->user_id_1 == $currentUserId ? 'request_sent' : 'request_received';
                }
            }
            return [
                'id' => $u->user_id,
                'name' => $u->display_name,
                'email' => $u->email,
                'avatar' => $u->avatar_url,
                'total_goals' => (int)($u->goals_count ?? 0),
                'total_notes' => (int)($u->notes_count ?? 0),
                'is_premium' => (bool)optional($u->profile)->is_premium,
                'friend_status' => $friendStatus,
            ];
        });

        return response()->json(['users' => $result]);
    }

    /**
     * Lấy danh sách cộng tác viên.
     */
    public function getCollaborators(Request $request)
    {
        $currentUserId = Auth::id();
        $goalIds = GoalCollaboration::where('user_id', $currentUserId)->pluck('goal_id');
        if ($goalIds->isEmpty()) return response()->json(['data' => []]);

        $collaboratorIds = GoalCollaboration::whereIn('goal_id', $goalIds)
            ->where('user_id', '!=', $currentUserId)
            ->distinct()->pluck('user_id');
        if ($collaboratorIds->isEmpty()) return response()->json(['data' => []]);
            
        $collaborators = User::whereIn('user_id', $collaboratorIds)
            ->withCount(['goals', 'notes'])
            ->get()->map(function ($user) use ($currentUserId) {
                $friendship = Friendship::where(function ($q) use ($currentUserId, $user) { $q->where('user_id_1', $currentUserId)->where('user_id_2', $user->user_id); })
                    ->orWhere(function ($q) use ($currentUserId, $user) { $q->where('user_id_1', $user->user_id)->where('user_id_2', $currentUserId); })->first();
                $friendStatus = 'not_friends';
                if ($friendship) {
                    if ($friendship->status == 'accepted') $friendStatus = 'friends';
                    elseif ($friendship->status == 'pending') $friendStatus = ($friendship->user_id_1 == $currentUserId) ? 'request_sent' : 'request_received';
                }
                return [
                    'id' => $user->user_id, 'name' => $user->display_name, 'email' => $user->email, 'avatar' => $user->avatar_url,
                    'total_goals' => $user->goals_count, 'total_notes' => $user->notes_count,
                    'is_premium' => (bool)optional($user->profile)->is_premium, 'friend_status' => $friendStatus,
                ];
            });
        return response()->json(['data' => $collaborators]);
    }
    
    /**
     * Gợi ý bạn bè dựa trên bạn chung.
     */
    public function getUserSuggestions(Request $request)
    {
        $currentUserId = Auth::id();

        $existingRelations = Friendship::where('user_id_1', $currentUserId)
            ->orWhere('user_id_2', $currentUserId)
            ->get();
        $exclusionIds = $existingRelations->flatMap(function ($friendship) use ($currentUserId) {
            return [$friendship->user_id_1, $friendship->user_id_2];
        })->unique()->toArray();

        $myFriendIds = $existingRelations->where('status', 'accepted')->flatMap(function ($friendship) use ($currentUserId) {
            return [$friendship->user_id_1, $friendship->user_id_2];
        })->unique()->except($currentUserId)->toArray();

        if (empty($myFriendIds)) {
            $suggestions = User::whereNotIn('user_id', $exclusionIds)
                ->inRandomOrder()->limit(10)->get();
        } else {
            $friendOfFriendIds = Friendship::where('status', 'accepted')
                ->where(function ($query) use ($myFriendIds) {
                    $query->whereIn('user_id_1', $myFriendIds)
                          ->orWhereIn('user_id_2', $myFriendIds);
                })
                ->get()
                ->flatMap(function ($friendship) {
                    return [$friendship->user_id_1, $friendship->user_id_2];
                })
                ->unique()
                ->diff($exclusionIds)
                ->values();

            if ($friendOfFriendIds->isEmpty()) {
                return response()->json(['users' => []]);
            }

            $mutualFriendsSubquery = Friendship::selectRaw('count(*)')
                ->where('status', 'accepted')
                ->where(function($query) use ($myFriendIds) {
                    $query->whereIn('user_id_1', $myFriendIds)
                          ->orWhereIn('user_id_2', $myFriendIds);
                })
                ->where(function($query) {
                    $query->whereColumn('user_id_1', 'Users.user_id')
                          ->orWhereColumn('user_id_2', 'Users.user_id');
                });

            $suggestions = User::whereIn('user_id', $friendOfFriendIds)
                ->select('*')
                ->selectSub($mutualFriendsSubquery, 'mutual_friends_count')
                ->withCount(['goals', 'notes'])
                ->orderByDesc('mutual_friends_count')
                ->limit(10)
                ->get();
        }

        $formattedSuggestions = $suggestions->map(function ($user) {
            return [
                'id' => $user->user_id,
                'name' => $user->display_name,
                'email' => $user->email,
                'avatar' => $user->avatar_url,
                'total_goals' => $user->goals_count ?? 0,
                'total_notes' => $user->notes_count ?? 0,
                'is_premium' => (bool)optional($user->profile)->is_premium,
                'mutual_friends_count' => $user->mutual_friends_count ?? 0,
                'friend_status' => 'not_friends',
            ];
        });

        return response()->json(['users' => $formattedSuggestions]);
    }
    
    /**
     * Gửi lời mời kết bạn bằng email (hàm cũ).
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