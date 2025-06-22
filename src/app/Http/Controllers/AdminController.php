<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Goal;
use App\Models\Admin;
use App\Models\UserProfile;
use App\Models\Note;
use App\Models\Event;
use App\Models\Notification;
use App\Models\UserSubscription;
use App\Models\SubscriptionPlan;
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
            'total_notes' => Note::count(),
            'total_events' => Event::count(),
            'recent_users' => User::with('profile')->latest('created_at')->take(5)->get(),
            'recent_goals' => Goal::with('user')->latest('created_at')->take(5)->get(),
        ];

        return inertia('Admin/Dashboard', [
            'stats' => $stats
        ]);
    }

    // Page methods for Inertia
    public function showDashboard()
    {
        return $this->dashboard();
    }

    public function showUsers(Request $request)
    {
        $users = User::with(['profile', 'admin'])
            ->when($request->search, function ($query, $search) {
                $query->where('display_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return inertia('Admin/Users', [
            'users' => $users,
            'filters' => $request->only('search')
        ]);
    }

    public function showGoals(Request $request)
    {
        $goals = Goal::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status && $request->status !== 'all', function ($query, $status) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        return inertia('Admin/Goals', [
            'goals' => $goals,
            'filters' => $request->only('search', 'status')
        ]);
    }

    public function showNotes(Request $request)
    {
        $notes = Note::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest('created_at')
            ->paginate(10);

        return inertia('Admin/Notes', [
            'notes' => $notes,
            'filters' => $request->only('search')
        ]);
    }

    public function showEvents(Request $request)
    {
        $events = Event::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest('created_at')
            ->paginate(10);

        return inertia('Admin/Events', [
            'events' => $events,
            'filters' => $request->only('search')
        ]);
    }

    public function showNotifications(Request $request)
    {
        $notifications = Notification::with('user')
            ->when($request->type && $request->type !== 'all', function ($query, $type) {
                $query->where('type', $request->type);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('content', 'like', "%{$search}%");
            })
            ->latest('created_at')
            ->paginate(10);

        return inertia('Admin/Notifications', [
            'notifications' => $notifications,
            'filters' => $request->only('search', 'type')
        ]);
    }

    public function showSubscriptions(Request $request)
    {
        $subscriptions = UserSubscription::with(['user', 'subscriptionPlan'])
            ->when($request->status && $request->status !== 'all', function ($query, $status) {
                $query->where('payment_status', $request->status);
            })
            ->latest('created_at')
            ->paginate(10);

        $plans = SubscriptionPlan::all();
        $stats = [
            'total_subscriptions' => UserSubscription::count(),
            'active_subscriptions' => UserSubscription::where('payment_status', 'active')->count(),
            'expired_subscriptions' => UserSubscription::where('payment_status', 'expired')->count(),
            'cancelled_subscriptions' => UserSubscription::where('payment_status', 'cancelled')->count(),
            'total_revenue' => UserSubscription::join('SubscriptionPlans', 'UserSubscriptions.plan_id', '=', 'SubscriptionPlans.plan_id')
                ->where('UserSubscriptions.payment_status', 'active')
                ->sum('SubscriptionPlans.price'),
        ];

        return inertia('Admin/Subscriptions', [
            'subscriptions' => $subscriptions,
            'plans' => $plans,
            'stats' => $stats,
            'filters' => $request->only('status')
        ]);
    }

    public function showReports(Request $request)
    {
        $reports = collect(); // Placeholder since Reports table not in models
        
        // User Statistics  
        $userStats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'premium_users' => UserProfile::where('is_premium', 1)->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        // Goal Statistics
        $goalStats = [
            'total_goals' => Goal::count(),
            'completed_goals' => Goal::where('status', 'completed')->count(),
            'active_goals' => Goal::where('status', 'in_progress')->count(),
            'new_goals_this_month' => Goal::whereMonth('created_at', now()->month)->count(),
        ];

        // Revenue Statistics
        $revenueStats = [
            'total_revenue' => UserSubscription::join('SubscriptionPlans', 'UserSubscriptions.plan_id', '=', 'SubscriptionPlans.plan_id')
                ->where('UserSubscriptions.payment_status', 'active')
                ->sum('SubscriptionPlans.price'),
            'monthly_revenue' => UserSubscription::join('SubscriptionPlans', 'UserSubscriptions.plan_id', '=', 'SubscriptionPlans.plan_id')
                ->where('UserSubscriptions.payment_status', 'active')
                ->whereMonth('UserSubscriptions.created_at', now()->month)
                ->sum('SubscriptionPlans.price'),
        ];

        return inertia('Admin/Reports', [
            'reports' => $reports,
            'userStats' => $userStats,
            'goalStats' => $goalStats,
            'revenueStats' => $revenueStats,
        ]);
    }

    public function showSettings()
    {
        return inertia('Admin/Settings');
    }

    // API methods for JSON responses
    public function apiDashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_goals' => Goal::count(),
            'active_goals' => Goal::where('status', 'in_progress')->count(),
            'completed_goals' => Goal::where('status', 'completed')->count(),
        ];

        return response()->json($stats);
    }

    public function users(Request $request)
    {
        $users = User::with(['goals', 'admin'])
            ->when($request->search, function ($query, $search) {
                $query->where('display_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(10);

        return response()->json($users);
    }

    public function goals(Request $request)
    {
        $goals = Goal::with(['user', 'collaborations'])
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
            'display_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:Users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'display_name' => $request->display_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'registration_type' => 'email',
            'status' => 'active',
        ]);

        Admin::create([
            'user_id' => $user->user_id,
            'role' => 'moderator',
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
            'email' => 'required|string|email|max:100|unique:Users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'display_name' => $request->display_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'registration_type' => 'email',
            'status' => 'active'
        ]);

        // Create user profile
        UserProfile::create([
            'user_id' => $user->user_id,
            'is_premium' => false
        ]);

        return response()->json([
            'message' => 'Collaborator created successfully',
            'user' => $user
        ], 201);
    }
} 