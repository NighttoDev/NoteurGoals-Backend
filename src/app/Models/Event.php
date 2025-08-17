<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'Events';
    protected $primaryKey = 'event_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'event_time',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'event_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(Goal::class, 'EventGoalLinks', 'event_id', 'goal_id');
    }

    // Event status helper methods
    public function isPast(): bool
    {
        return $this->event_time->isPast();
    }

    public function isToday(): bool
    {
        return $this->event_time->isToday();
    }

    public function isFuture(): bool
    {
        return $this->event_time->isFuture();
    }

    public function isUpcoming(): bool
    {
        return $this->event_time->isFuture() && $this->event_time->diffInDays() <= 7;
    }

    public function getStatusText(): string
    {
        if ($this->isPast()) {
            return 'Past';
        } elseif ($this->isToday()) {
            return 'Today';
        } else {
            return 'Upcoming';
        }
    }

    public function getTimeUntilEvent(): string
    {
        if ($this->isPast()) {
            return 'Đã qua ' . $this->event_time->diffForHumans();
        } elseif ($this->isToday()) {
            return 'Hôm nay lúc ' . $this->event_time->format('H:i');
        } else {
            return $this->event_time->diffForHumans();
        }
    }

    // Goal linking methods
    public function linkGoal(int $goalId): bool
    {
        if ($this->isLinkedToGoal($goalId)) {
            return false; // Already linked
        }

        return DB::table('EventGoalLinks')->insert([
            'event_id' => $this->event_id,
            'goal_id' => $goalId,
            'created_at' => now()
        ]);
    }

    public function unlinkGoal(int $goalId): bool
    {
        return DB::table('EventGoalLinks')
            ->where('event_id', $this->event_id)
            ->where('goal_id', $goalId)
            ->delete() > 0;
    }

    public function isLinkedToGoal(int $goalId): bool
    {
        return DB::table('EventGoalLinks')
            ->where('event_id', $this->event_id)
            ->where('goal_id', $goalId)
            ->exists();
    }

    // Check if event has goal links
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
    public function scopePast($query)
    {
        return $query->where('event_time', '<', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('event_time', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_time', '>', now());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('event_time', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }
}
