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
        return $this->belongsToMany(Goal::class, 'note_goal_links', 'note_id', 'goal_id');
    }

    public function milestones(): BelongsToMany
    {
        return $this->belongsToMany(Milestone::class, 'note_milestone_links', 'note_id', 'milestone_id');
    }
}
