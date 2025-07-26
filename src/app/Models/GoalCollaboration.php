<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalCollaboration extends Model
{
    protected $table = 'GoalCollaboration'; // Tên bảng trong cơ sở dữ liệu
    protected $primaryKey = 'collab_id';
    public $timestamps = false;

    protected $fillable = [
        'goal_id',
        'user_id',
        'role',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal_id', 'goal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
