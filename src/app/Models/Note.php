<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Note extends Model
{
    protected $table = 'Notes';
    protected $primaryKey = 'note_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'title',
        'content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(Goal::class, 'NoteGoalLinks', 'note_id', 'goal_id');
    }

    public function milestones(): BelongsToMany
    {
        return $this->belongsToMany(Milestone::class, 'NoteMilestoneLinks', 'note_id', 'milestone_id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'FileNoteLinks', 'note_id', 'file_id');
    }

    // Helper methods
    public function getWordCount()
    {
        return str_word_count(strip_tags($this->content ?? ''));
    }

    public function getReadingTime()
    {
        $words = $this->getWordCount();
        $wordsPerMinute = 200; // Average reading speed
        $minutes = ceil($words / $wordsPerMinute);
        return max($minutes, 1);
    }

    public function hasContent()
    {
        return !empty(trim($this->content ?? ''));
    }

    public function isLinkedToGoal($goalId = null)
    {
        if ($goalId) {
            return $this->goals()->where('Goals.goal_id', $goalId)->exists();
        }
        return $this->goals()->exists();
    }

    public function isLinkedToMilestone($milestoneId = null)
    {
        if ($milestoneId) {
            return $this->milestones()->where('Milestones.milestone_id', $milestoneId)->exists();
        }
        return $this->milestones()->exists();
    }

    public function hasAttachments()
    {
        return $this->files()->exists();
    }
}
