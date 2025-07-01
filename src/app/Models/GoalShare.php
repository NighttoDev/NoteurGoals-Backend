<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalShare extends Model
{
    protected $table = 'GoalShares';
    protected $primaryKey = 'share_id';
    public $timestamps = false;

    protected $fillable = [
        'goal_id',
        'share_type',
        'shared_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal_id', 'goal_id');
    }
}
