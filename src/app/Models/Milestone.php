<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Milestone extends Model
{
    protected $table = 'Milestones';
    protected $primaryKey = 'milestone_id';
    public $timestamps = true;

    protected $fillable = [
        'goal_id',
        'title',
        'deadline',
        'is_completed',
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_completed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal_id', 'goal_id');
    }

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'NoteMilestoneLinks', 'milestone_id', 'note_id');
    }
}
