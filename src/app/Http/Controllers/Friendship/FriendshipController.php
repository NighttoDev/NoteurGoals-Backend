<?php

namespace App\Http\Controllers\Friendship;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FriendshipController extends Controller
{
    public function index(Request $request)
    {
        $friends = Friendship::where(function ($q) {
            $q->where('user_id_1', Auth::id())
              ->orWhere('user_id_2', Auth::id());
        })
        ->where('status', 'accepted')
        ->paginate(10);

        return response()->json($friends);
    }

    public function sendRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:Users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (Auth::id() == $request->user_id) {
            return response()->json(['message' => 'Cannot friend yourself'], 400);
        }

        $friendship = Friendship::create([
            'user_id_1' => Auth::id(),
            'user_id_2' => $request->user_id,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Friend request sent', 'friendship' => $friendship], 201);
    }

    public function respond(Request $request, Friendship $friendship)
    {
        if ($friendship->user_id_2 !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $friendship->status = $request->status;
        $friendship->save();

        return response()->json(['message' => 'Friend request updated', 'friendship' => $friendship]);
    }

    public function destroy(Friendship $friendship)
    {
        if ($friendship->user_id_1 !== Auth::id() && $friendship->user_id_2 !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $friendship->delete();

        return response()->json(['message' => 'Friend removed']);
    }
}
