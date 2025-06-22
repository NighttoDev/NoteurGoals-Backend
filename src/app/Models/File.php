<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class File extends Model
{
    protected $table = 'Files';
    protected $primaryKey = 'file_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(Goal::class, 'file_goal_links', 'file_id', 'goal_id');
    }

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'file_note_links', 'file_id', 'note_id');
    }
}
