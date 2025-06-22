<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalProgress extends Model
{
    protected $table = 'GoalProgress';
    protected $primaryKey = 'progress_id';
    public $timestamps = false;

    protected $fillable = [
        'goal_id',
        'progress_value',
        'updated_at',
    ];

    protected $casts = [
        'progress_value' => 'float',
        'updated_at' => 'datetime',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal_id', 'goal_id');
    }
}
