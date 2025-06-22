<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalProgress extends Model
{
    public $timestamps = false;
    protected $table = 'GoalProgress';
    protected $primaryKey = 'progress_id';

    protected $fillable = [
        'goal_id',
        'progress_value'
    ];

    protected $casts = [
        'progress_value' => 'float',
        'updated_at' => 'datetime'
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal_id', 'goal_id');
    }
}
