<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $table = 'Events';
    protected $primaryKey = 'event_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'event_time',
    ];

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
        return $this->belongsToMany(Goal::class, 'event_goal_links', 'event_id', 'goal_id');
    }
}
