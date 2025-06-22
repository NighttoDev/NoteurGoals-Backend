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
            'active_users' => User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(30))->count(),
            'completed_goals' => Goal::where('status', 'completed')->count(),
            'premium_users' => UserProfile::where('is_premium', 1)->count(),
            'monthly_revenue' => UserSubscription::join('SubscriptionPlans', 'UserSubscriptions.plan_id', '=', 'SubscriptionPlans.plan_id')
                ->where('UserSubscriptions.payment_status', 'active')
                ->whereMonth('UserSubscriptions.created_at', now()->month)
                ->sum('SubscriptionPlans.price'),
            'recent_users' => User::with('profile')->latest('created_at')->take(5)->get()->map(function($user) {
                return [
                    'user_id' => $user->user_id,
                    'display_name' => $user->profile?->display_name ?? $user->display_name ?? $user->name,
                    'name' => $user->display_name ?? $user->email,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'status' => $user->status,
                    'is_premium' => $user->profile ? $user->profile->is_premium : false
                ];
            }),
            'recent_goals' => Goal::with('user.profile')->latest('created_at')->take(5)->get()->map(function($goal) {
                return [
                    'goal_id' => $goal->goal_id,
                    'title' => $goal->title,
                    'status' => $goal->status,
                    'created_at' => $goal->created_at,
                    'user_name' => $goal->user ? ($goal->user->profile?->display_name ?? $goal->user->display_name ?? $goal->user->email) : 'Unknown User',
                    'user' => [
                        'display_name' => $goal->user ? ($goal->user->profile?->display_name ?? $goal->user->display_name ?? $goal->user->email) : 'Unknown User'
                    ]
                ];
            }),
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
        $users = User::with(['profile'])
            ->when($request->search, function ($query, $search) {
                $query->where('display_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->through(function($user) {
                return [
                    'user_id' => $user->user_id,
                    'name' => $user->display_name ?? $user->email,
                    'email' => $user->email,
                    'status' => $user->status,
                    'created_at' => $user->created_at,
                    'avatar_url' => $user->avatar_url,
                    'registration_type' => $user->registration_type,
                    'last_login_at' => $user->last_login_at,
                    'is_premium' => $user->profile ? $user->profile->is_premium : false,
                    'goals_count' => Goal::where('user_id', $user->user_id)->count(),
                    'notes_count' => Note::where('user_id', $user->user_id)->count()
                ];
            });

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
            ->paginate(10)
            ->through(function($goal) {
                return [
                    'goal_id' => $goal->goal_id,
                    'title' => $goal->title,
                    'description' => $goal->description,
                    'status' => $goal->status,
                    'start_date' => $goal->start_date,
                    'end_date' => $goal->end_date,
                    'created_at' => $goal->created_at,
                    'user' => $goal->user ? [
                        'user_id' => $goal->user->user_id,
                        'name' => $goal->user->display_name ?? $goal->user->email,
                        'email' => $goal->user->email
                    ] : null,
                    'milestones_count' => $goal->milestones()->count()
                ];
            });

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
            ->paginate(10)
            ->through(function($note) {
                return [
                    'note_id' => $note->note_id,
                    'title' => $note->title,
                    'content' => $note->content,
                    'created_at' => $note->created_at,
                    'updated_at' => $note->updated_at,
                    'user' => $note->user ? [
                        'user_id' => $note->user->user_id,
                        'name' => $note->user->display_name ?? $note->user->email,
                        'email' => $note->user->email
                    ] : null
                ];
            });

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
            ->paginate(10)
            ->through(function($event) {
                return [
                    'event_id' => $event->event_id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_time' => $event->event_time,
                    'created_at' => $event->created_at,
                    'user' => $event->user ? [
                        'user_id' => $event->user->user_id,
                        'name' => $event->user->display_name ?? $event->user->email,
                        'email' => $event->user->email
                    ] : null
                ];
            });

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
            ->paginate(10)
            ->through(function($notification) {
                return [
                    'notification_id' => $notification->notification_id,
                    'type' => $notification->type,
                    'content' => $notification->content,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at,
                    'user' => $notification->user ? [
                        'user_id' => $notification->user->user_id,
                        'name' => $notification->user->display_name ?? $notification->user->email,
                        'email' => $notification->user->email
                    ] : null
                ];
            });

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
            ->paginate(10)
            ->through(function($subscription) {
                return [
                    'subscription_id' => $subscription->subscription_id,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'payment_status' => $subscription->payment_status,
                    'auto_renewal_id' => $subscription->auto_renewal_id,
                    'renewal_count' => $subscription->renewal_count,
                    'created_at' => $subscription->created_at,
                    'user' => $subscription->user ? [
                        'user_id' => $subscription->user->user_id,
                        'name' => $subscription->user->display_name ?? $subscription->user->email,
                        'email' => $subscription->user->email
                    ] : null,
                    'plan' => $subscription->subscriptionPlan ? [
                        'plan_id' => $subscription->subscriptionPlan->plan_id,
                        'name' => $subscription->subscriptionPlan->name,
                        'price' => $subscription->subscriptionPlan->price,
                        'duration' => $subscription->subscriptionPlan->duration
                    ] : null
                ];
            });

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