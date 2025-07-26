<?php

namespace App\Http\Controllers\AISuggestion;

use App\Http\Controllers\Controller;
use App\Models\AISuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AISuggestionController extends Controller
{
    public function index(Request $request)
    {
        $suggestions = Auth::user()->AISuggestions()
            ->when($request->has('is_read'), function ($query) use ($request) {
                $query->where('is_read', $request->is_read);
            })
            ->when($request->has('suggestion_type'), function ($query) use ($request) {
                $query->where('suggestion_type', $request->suggestion_type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($suggestions);
    }

    public function show(AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($suggestion->load('goals'));
    }

    public function markAsRead(AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $suggestion->markAsRead();

        return response()->json(['message' => 'Suggestion marked as read']);
    }

    public function markAsUnread(AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $suggestion->markAsUnread();

        return response()->json(['message' => 'Suggestion marked as unread']);
    }

    // Goal linking methods
    public function linkGoal(Request $request, AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'goal_id' => 'required|exists:Goals,goal_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $goal = \App\Models\Goal::find($request->goal_id);
        
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($suggestion->linkGoal($request->goal_id)) {
            return response()->json(['message' => 'Goal linked to suggestion successfully']);
        } else {
            return response()->json(['message' => 'Goal is already linked to this suggestion'], 409);
        }
    }

    public function unlinkGoal(AISuggestion $suggestion, $goalId)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($suggestion->unlinkGoal($goalId)) {
            return response()->json(['message' => 'Goal unlinked from suggestion successfully']);
        } else {
            return response()->json(['message' => 'Goal was not linked to this suggestion'], 404);
        }
    }

    // Get suggestion with all its links
    public function showWithLinks(AISuggestion $suggestion)
    {
        if ($suggestion->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($suggestion->load('goals'));
    }

    // Mark all suggestions as read
    public function markAllAsRead()
    {
        Auth::user()->AISuggestions()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All suggestions marked as read']);
    }

    // Get unread count
    public function getUnreadCount()
    {
        $count = Auth::user()->AISuggestions()
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    // ========================================
    // NEW AI INTEGRATION METHODS
    // ========================================

    /**
     * Trigger AI analysis for a specific goal
     */
    public function analyzeGoal(Request $request, \App\Models\Goal $goal)
    {
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $aiService = app(\App\Services\AIService::class);
        
        // Get user context from request or build default
        $userContext = $request->get('context', []);
        
        try {
            $analysis = $aiService->analyzeGoalBreakdown($goal, $userContext);
            
            return response()->json([
                'message' => 'Goal analysis completed',
                'analysis' => $analysis,
                'suggestion_created' => true
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Analysis failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get priority suggestions for current user
     */
    public function getPrioritySuggestions()
    {
        $aiService = app(\App\Services\AIService::class);
        
        try {
            $suggestions = $aiService->getPrioritySuggestions(Auth::user());
            
            return response()->json([
                'message' => 'Priority suggestions generated',
                'suggestions' => $suggestions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get priority suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get completion forecast for current user
     */
    public function getCompletionForecast()
    {
        $aiService = app(\App\Services\AIService::class);
        
        try {
            $forecast = $aiService->predictCompletion(Auth::user());
            
            return response()->json([
                'message' => 'Completion forecast generated',
                'forecast' => $forecast
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate forecast',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get personalized insights for current user
     */
    public function getUserInsights()
    {
        $aiService = app(\App\Services\AIService::class);
        
        try {
            $insights = $aiService->getUserInsights(Auth::user());
            
            return response()->json([
                'message' => 'User insights generated',
                'insights' => $insights
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get insights',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually trigger AI suggestions generation
     */
    public function generateSuggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'types' => 'array',
            'types.*' => 'in:goal_breakdown,priority,completion_forecast',
            'goal_id' => 'nullable|exists:Goals,goal_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $aiService = app(\App\Services\AIService::class);
        $user = Auth::user();
        $types = $request->get('types', ['priority', 'completion_forecast']);
        $results = [];

        try {
            foreach ($types as $type) {
                switch ($type) {
                    case 'goal_breakdown':
                        if ($request->goal_id) {
                            $goal = \App\Models\Goal::find($request->goal_id);
                            if ($goal && $goal->user_id === $user->user_id) {
                                $results[$type] = $aiService->analyzeGoalBreakdown($goal);
                            }
                        }
                        break;
                        
                    case 'priority':
                        $results[$type] = $aiService->getPrioritySuggestions($user);
                        break;
                        
                    case 'completion_forecast':
                        $results[$type] = $aiService->predictCompletion($user);
                        break;
                }
            }

            return response()->json([
                'message' => 'AI suggestions generated successfully',
                'results' => $results,
                'generated_count' => count($results)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate some suggestions',
                'error' => $e->getMessage(),
                'partial_results' => $results
            ], 500);
        }
    }

    /**
     * Check AI service health
     */
    public function getAIServiceStatus()
    {
        $aiService = app(\App\Services\AIService::class);
        
        $isHealthy = $aiService->isHealthy();
        
        return response()->json([
            'ai_service_status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString()
        ]);
    }

    // Get suggestions by type
    public function getByType(Request $request, $type)
    {
        $validTypes = ['goal_breakdown', 'priority', 'completion_forecast'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['message' => 'Invalid suggestion type'], 400);
        }

        $suggestions = Auth::user()->AISuggestions()
            ->byType($type)
            ->when($request->has('is_read'), function ($query) use ($request) {
                $query->where('is_read', $request->is_read);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($suggestions);
    }
}
