<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class AISuggestion extends Model
{
    protected $table = 'AISuggestions';
    protected $primaryKey = 'suggestion_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'suggestion_type',
        'content',
        'is_read',
        'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(Goal::class, 'AISuggestionGoalLinks', 'suggestion_id', 'goal_id');
    }

    // Suggestion type helper methods
    public function isGoalBreakdown(): bool
    {
        return $this->suggestion_type === 'goal_breakdown';
    }

    public function isPriority(): bool
    {
        return $this->suggestion_type === 'priority';
    }

    public function isCompletionForecast(): bool
    {
        return $this->suggestion_type === 'completion_forecast';
    }

    public function getTypeText(): string
    {
        return match($this->suggestion_type) {
            'goal_breakdown' => 'Phân tích mục tiêu',
            'priority' => 'Ưu tiên',
            'completion_forecast' => 'Dự báo hoàn thành',
            default => 'Khác'
        };
    }

    // Read status methods
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    public function markAsUnread(): bool
    {
        return $this->update(['is_read' => false]);
    }

    // Content helper methods
    public function getShortContent(int $length = 100): string
    {
        return strlen($this->content) > $length 
            ? substr($this->content, 0, $length) . '...' 
            : $this->content;
    }

    // Goal linking methods
    public function linkGoal(int $goalId): bool
    {
        if ($this->isLinkedToGoal($goalId)) {
            return false; // Already linked
        }

        return DB::table('AISuggestionGoalLinks')->insert([
            'suggestion_id' => $this->suggestion_id,
            'goal_id' => $goalId,
            'created_at' => now()
        ]);
    }

    public function unlinkGoal(int $goalId): bool
    {
        return DB::table('AISuggestionGoalLinks')
            ->where('suggestion_id', $this->suggestion_id)
            ->where('goal_id', $goalId)
            ->delete() > 0;
    }

    public function isLinkedToGoal(int $goalId): bool
    {
        return DB::table('AISuggestionGoalLinks')
            ->where('suggestion_id', $this->suggestion_id)
            ->where('goal_id', $goalId)
            ->exists();
    }

    // Check if suggestion has goal links
    public function hasGoalLinks(): bool
    {
        return $this->goals()->exists();
    }

    // Get link counts
    public function getGoalLinksCount(): int
    {
        return $this->goals()->count();
    }

    // Scope methods for filtering
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('suggestion_type', $type);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
