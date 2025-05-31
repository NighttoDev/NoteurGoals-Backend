<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Goal;
use App\Models\Admin;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_goals' => Goal::count(),
            'active_goals' => Goal::where('status', 'active')->count(),
            'completed_goals' => Goal::where('status', 'completed')->count(),
        ];

        return response()->json($stats);
    }

    public function users(Request $request)
    {
        $users = User::with(['goals', 'admin'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(10);

        return response()->json($users);
    }

    public function goals(Request $request)
    {
        $goals = Goal::with(['user', 'collaborators'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->paginate(10);

        return response()->json($goals);
    }

    public function createAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Admin::create([
            'user_id' => $user->id,
            'role' => 'admin',
        ]);

        return response()->json([
            'message' => 'Admin created successfully',
            'user' => $user
        ], 201);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->admin) {
            return response()->json([
                'message' => 'Cannot delete admin user'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function deleteGoal($id)
    {
        $goal = Goal::findOrFail($id);
        $goal->delete();

        return response()->json([
            'message' => 'Goal deleted successfully'
        ]);
    }

    public function createCollaborator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password_hash' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'display_name' => $request->display_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password_hash),
            'registration_type' => 'email',
            'status' => 'active'
        ]);

        // Create user profile
        UserProfile::create([
            'user_id' => $user->id,
            'is_premium' => false
        ]);

        return response()->json([
            'message' => 'Collaborator created successfully',
            'user' => $user
        ], 201);
    }
} 