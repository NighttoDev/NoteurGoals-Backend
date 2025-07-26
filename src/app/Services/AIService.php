<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Goal;
use App\Models\AISuggestion;
use Exception;

class AIService
{
    private $aiServiceUrl;
    private $timeout;

    public function __construct()
    {
        $this->aiServiceUrl = config('services.ai_microservice.url', 'http://localhost:8001');
        $this->timeout = config('services.ai_microservice.timeout', 30);
    }

    /**
     * Phân tích và chia nhỏ mục tiêu
     */
    public function analyzeGoalBreakdown(Goal $goal, array $userContext = [])
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->aiServiceUrl}/api/v1/analyze/goal-breakdown", [
                    'goal_id' => $goal->goal_id,
                    'title' => $goal->title,
                    'description' => $goal->description,
                    'start_date' => $goal->start_date->format('Y-m-d'),
                    'end_date' => $goal->end_date->format('Y-m-d'),
                    'user_context' => $userContext
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // Lưu suggestions vào database
                $this->saveSuggestion(
                    $goal->user_id,
                    'goal_breakdown',
                    $this->formatBreakdownSuggestion($result),
                    $goal->goal_id
                );
                
                return $result;
            }

            throw new Exception("AI Service responded with status: " . $response->status());
            
        } catch (Exception $e) {
            Log::error('AI Goal Breakdown Analysis failed', [
                'goal_id' => $goal->goal_id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to rule-based analysis
            return $this->fallbackGoalBreakdown($goal);
        }
    }

    /**
     * Gợi ý ưu tiên mục tiêu
     */
    public function getPrioritySuggestions(User $user)
    {
        try {
            // Chuẩn bị dữ liệu user
            $userData = $this->prepareUserData($user);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->aiServiceUrl}/api/v1/analyze/priority-suggestions", $userData);

            if ($response->successful()) {
                $result = $response->json();
                
                // Lưu suggestions
                $this->saveSuggestion(
                    $user->user_id,
                    'priority',
                    $this->formatPrioritySuggestion($result)
                );
                
                return $result;
            }

            throw new Exception("AI Service responded with status: " . $response->status());
            
        } catch (Exception $e) {
            Log::error('AI Priority Suggestions failed', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);
            
            return $this->fallbackPrioritySuggestions($user);
        }
    }

    /**
     * Dự đoán khả năng hoàn thành
     */
    public function predictCompletion(User $user)
    {
        try {
            $userData = $this->prepareUserData($user);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->aiServiceUrl}/api/v1/analyze/completion-forecast", $userData);

            if ($response->successful()) {
                $result = $response->json();
                
                // Lưu suggestions
                $this->saveSuggestion(
                    $user->user_id,
                    'completion_forecast',
                    $this->formatCompletionForecast($result)
                );
                
                return $result;
            }

            throw new Exception("AI Service responded with status: " . $response->status());
            
        } catch (Exception $e) {
            Log::error('AI Completion Forecast failed', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);
            
            return $this->fallbackCompletionForecast($user);
        }
    }

    /**
     * Lấy insights cá nhân hóa
     */
    public function getUserInsights(User $user)
    {
        try {
            // Cache insights trong 1 giờ
            $cacheKey = "user_insights_{$user->user_id}";
            
            return Cache::remember($cacheKey, 3600, function () use ($user) {
                $response = Http::timeout($this->timeout)
                    ->get("{$this->aiServiceUrl}/api/v1/insights/user/{$user->user_id}");

                if ($response->successful()) {
                    return $response->json();
                }

                throw new Exception("AI Service responded with status: " . $response->status());
            });
            
        } catch (Exception $e) {
            Log::error('AI User Insights failed', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);
            
            return $this->fallbackUserInsights($user);
        }
    }

    /**
     * Xử lý batch analysis cho multiple users
     */
    public function batchAnalyzeUsers(array $userIds)
    {
        try {
            $response = Http::timeout(120) // Tăng timeout cho batch
                ->post("{$this->aiServiceUrl}/api/v1/batch/analyze-all-users", ['user_ids' => $userIds]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("AI Service responded with status: " . $response->status());
            
        } catch (Exception $e) {
            Log::error('AI Batch Analysis failed', [
                'user_ids' => $userIds,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Chuẩn bị dữ liệu user để gửi đến AI service
     */
    private function prepareUserData(User $user): array
    {
        $goals = $user->goals()->with(['milestones', 'progress'])->get();
        $milestones = $user->goals()->with('milestones')->get()
            ->pluck('milestones')->flatten();

        // Thu thập behavior metrics
        $behaviorMetrics = [
            'total_goals' => $goals->count(),
            'completed_goals' => $goals->where('status', 'completed')->count(),
            'avg_progress' => $goals->avg(function ($goal) {
                return $goal->progress ? $goal->progress->progress_value : 0;
            }),
            'days_since_registration' => $user->created_at->diffInDays(now()),
            'last_activity' => $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : null,
        ];

        return [
            'user_id' => $user->user_id,
            'goals' => $goals->toArray(),
            'milestones' => $milestones->toArray(),
            'behavior_metrics' => $behaviorMetrics
        ];
    }

    /**
     * Lưu AI suggestion vào database
     */
    private function saveSuggestion(int $userId, string $type, string $content, int $goalId = null)
    {
        $suggestion = AISuggestion::create([
            'user_id' => $userId,
            'suggestion_type' => $type,
            'content' => $content,
            'is_read' => false,
            'created_at' => now()
        ]);

        // Link với goal nếu có
        if ($goalId && $suggestion) {
            $suggestion->linkGoal($goalId);
        }

        return $suggestion;
    }

    /**
     * Format breakdown suggestion thành văn bản tiếng Việt
     */
    private function formatBreakdownSuggestion(array $result): string
    {
        $content = "🎯 **Phân tích mục tiêu: {$result['goal_id']}**\n\n";
        $content .= "📊 **Độ phức tạp:** {$result['complexity_score']}/10\n\n";
        
        if (!empty($result['suggested_milestones'])) {
            $content .= "✅ **Các bước thực hiện đề xuất:**\n";
            foreach ($result['suggested_milestones'] as $index => $milestone) {
                $content .= "   " . ($index + 1) . ". {$milestone['title']}\n";
                if (isset($milestone['estimated_days'])) {
                    $content .= "      ⏱️ Ước tính: {$milestone['estimated_days']} ngày\n";
                }
            }
            $content .= "\n";
        }
        
        if (!empty($result['recommendations'])) {
            $content .= "💡 **Gợi ý:**\n";
            foreach ($result['recommendations'] as $recommendation) {
                $content .= "   • {$recommendation}\n";
            }
        }
        
        $content .= "\n🤖 *Độ tin cậy: " . ($result['confidence'] * 100) . "%*";
        
        return $content;
    }

    /**
     * Format priority suggestion
     */
    private function formatPrioritySuggestion(array $result): string
    {
        $content = "⭐ **Gợi ý ưu tiên mục tiêu**\n\n";
        
        if (!empty($result['urgent_goals'])) {
            $content .= "🚨 **Mục tiêu cần ưu tiên:**\n";
            foreach ($result['urgent_goals'] as $goal) {
                $content .= "   • {$goal['title']} (Urgency: {$goal['urgency']})\n";
            }
            $content .= "\n";
        }
        
        if (!empty($result['recommendations'])) {
            $content .= "💡 **Khuyến nghị:**\n";
            foreach ($result['recommendations'] as $rec) {
                $content .= "   • {$rec}\n";
            }
        }
        
        $content .= "\n🤖 *Độ tin cậy: " . ($result['confidence'] * 100) . "%*";
        
        return $content;
    }

    /**
     * Format completion forecast
     */
    private function formatCompletionForecast(array $result): string
    {
        $content = "🔮 **Dự báo hoàn thành mục tiêu**\n\n";
        $content .= "📈 **Tỉ lệ thành công tổng thể:** " . ($result['overall_success_rate'] * 100) . "%\n\n";
        
        if (!empty($result['predictions'])) {
            $content .= "📊 **Chi tiết từng mục tiêu:**\n";
            foreach ($result['predictions'] as $prediction) {
                $content .= "   🎯 Goal ID: {$prediction['goal_id']}\n";
                $content .= "   📊 Tiến độ hiện tại: {$prediction['current_progress']}%\n";
                $content .= "   🎲 Khả năng hoàn thành: " . ($prediction['completion_probability'] * 100) . "%\n";
                if (isset($prediction['estimated_completion_date'])) {
                    $content .= "   📅 Dự kiến hoàn thành: {$prediction['estimated_completion_date']}\n";
                }
                $content .= "\n";
            }
        }
        
        $content .= "🤖 *Độ tin cậy: " . ($result['confidence'] * 100) . "%*";
        
        return $content;
    }

    // ========================================
    // FALLBACK METHODS (when AI service fails)
    // ========================================

    private function fallbackGoalBreakdown(Goal $goal): array
    {
        // Rule-based fallback
        $daysToComplete = $goal->start_date->diffInDays($goal->end_date);
        $complexity = min(10, max(1, $daysToComplete / 7)); // 1 week = 1 complexity point
        
        $milestones = [];
        if ($daysToComplete > 7) {
            $stepCount = min(5, max(2, floor($daysToComplete / 7)));
            for ($i = 1; $i <= $stepCount; $i++) {
                $milestones[] = [
                    'title' => "Bước {$i}: Hoàn thành " . ($i * 100 / $stepCount) . "% mục tiêu",
                    'estimated_days' => floor($daysToComplete / $stepCount)
                ];
            }
        }
        
        return [
            'goal_id' => $goal->goal_id,
            'complexity_score' => $complexity,
            'suggested_milestones' => $milestones,
            'recommendations' => [
                'Chia nhỏ mục tiêu thành các bước cụ thể',
                'Đặt deadline rõ ràng cho từng milestone',
                'Review tiến độ hàng tuần'
            ],
            'confidence' => 0.6
        ];
    }

    private function fallbackPrioritySuggestions(User $user): array
    {
        $goals = $user->goals()->where('status', '!=', 'completed')->get();
        
        $urgentGoals = $goals->filter(function ($goal) {
            return $goal->end_date->diffInDays(now()) <= 7;
        })->map(function ($goal) {
            return [
                'goal_id' => $goal->goal_id,
                'title' => $goal->title,
                'urgency' => 0.9
            ];
        });
        
        return [
            'user_id' => $user->user_id,
            'urgent_goals' => $urgentGoals->toArray(),
            'recommendations' => [
                'Tập trung vào mục tiêu có deadline gần nhất',
                'Hoàn thành các mục tiêu nhỏ trước',
                'Đừng tham vọng quá nhiều mục tiêu cùng lúc'
            ],
            'confidence' => 0.5
        ];
    }

    private function fallbackCompletionForecast(User $user): array
    {
        $goals = $user->goals()->with('progress')->get();
        $avgProgress = $goals->avg(function ($goal) {
            return $goal->progress ? $goal->progress->progress_value : 0;
        });
        
        return [
            'user_id' => $user->user_id,
            'overall_success_rate' => min(1.0, $avgProgress / 100),
            'predictions' => [],
            'confidence' => 0.4
        ];
    }

    private function fallbackUserInsights(User $user): array
    {
        return [
            'user_id' => $user->user_id,
            'productivity_patterns' => [
                'most_productive_day' => 'Thứ 2',
                'avg_session_duration' => 45
            ],
            'user_profile' => [
                'strengths' => ['Tính kiên trì', 'Lập kế hoạch tốt'],
                'improvement_areas' => ['Quản lý thời gian', 'Tập trung']
            ],
            'personalized_tips' => [
                'Lập lịch cố định cho việc review mục tiêu',
                'Chia nhỏ các tác vụ lớn thành các bước nhỏ hơn'
            ],
            'confidence' => 0.3
        ];
    }

    /**
     * Check if AI service is healthy
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->aiServiceUrl}/health");
            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
} 