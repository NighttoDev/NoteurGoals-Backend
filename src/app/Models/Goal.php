<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Goal extends Model
{
    protected $table = 'Goals';
    protected $primaryKey = 'goal_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function progress(): HasOne
    {
        return $this->hasOne(GoalProgress::class);
    }

    public function notes()
    {
        return $this->belongsToMany(Note::class, 'note_goal_links');
    }

    public function files()
    {
        return $this->belongsToMany(File::class, 'file_goal_links');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_goal_links');
    }

    public function aiSuggestions()
    {
        return $this->belongsToMany(AISuggestion::class, 'ai_suggestion_goal_links');
    }

    public function share()
    {
        return $this->hasOne(GoalShare::class);
    }

    public function collaborations()
    {
        return $this->hasMany(GoalCollaboration::class);
    }

    // Helper methods
    public function calculateProgress()
    {
        $totalMilestones = $this->milestones()->count();
        if ($totalMilestones === 0) {
            return 0;
        }

        $completedMilestones = $this->milestones()->where('is_completed', true)->count();
        return ($completedMilestones / $totalMilestones) * 100;
    }

    public function updateStatus()
    {
        $progress = $this->calculateProgress();
        
        if ($progress === 100) {
            $this->status = 'completed';
        } elseif ($progress > 0) {
            $this->status = 'in_progress';
        } else {
            $this->status = 'new';
        }

        $this->save();
    }

    public function isShared()
    {
        return $this->share && $this->share->share_type !== 'private';
    }

    public function canBeAccessedBy(User $user)
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        if ($this->isShared()) {
            if ($this->share->share_type === 'public') {
                return true;
            }

            if ($this->share->share_type === 'friends') {
                return $this->user->friendships()
                    ->where(function ($query) use ($user) {
                        $query->where('user_id_2', $user->id)
                            ->where('status', 'accepted');
                    })
                    ->exists();
            }

            if ($this->share->share_type === 'collaboration') {
                return $this->collaborations()
                    ->where('user_id', $user->id)
                    ->exists();
            }
        }

        return false;
    }
} 