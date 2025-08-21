<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'Goals';
    protected $primaryKey = 'goal_id';
    public $timestamps = true;

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
    public function getRouteKeyName()
    {
        return 'goal_id';
    }
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'goal_id', 'goal_id');
    }

    public function progress(): HasOne
    {
        return $this->hasOne(GoalProgress::class, 'goal_id', 'goal_id');
    }

    public function notes()
    {
        return $this->belongsToMany(Note::class, 'NoteGoalLinks', 'goal_id', 'note_id');
    }

    public function files()
    {
        return $this->belongsToMany(File::class, 'FileGoalLinks', 'goal_id', 'file_id');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'EventGoalLinks', 'goal_id', 'event_id');
    }

    public function aiSuggestions()
    {
        return $this->belongsToMany(AISuggestion::class, 'AISuggestionGoalLinks', 'goal_id', 'suggestion_id');
    }

    public function share()
    {
        return $this->hasOne(GoalShare::class, 'goal_id', 'goal_id');
    }

    public function collaborations()
    {
        return $this->hasMany(GoalCollaboration::class, 'goal_id', 'goal_id')->with('user');
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
        if ($this->user_id === $user->user_id) {
            return true;
        }

        if ($this->isShared()) {
            if ($this->share->share_type === 'public') {
                return true;
            }

            if ($this->share->share_type === 'friends') {
                return $this->user->friendships()
                    ->where('user_id_2', $user->user_id)
                    ->where('status', 'accepted')
                    ->exists()
                || $this->user->friendshipRequests()
                    ->where('user_id_1', $user->user_id) 
                    ->where('status', 'accepted')
                    ->exists();
            }

            if ($this->share->share_type === 'collaboration') {
                return $this->collaborations()
                    ->where('user_id', $user->user_id)
                    ->exists();
            }
        }

        return false;
    }
} 