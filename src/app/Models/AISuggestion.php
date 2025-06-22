<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        return $this->belongsToMany(Goal::class, 'ai_suggestion_goal_links', 'suggestion_id', 'goal_id');
    }
}
