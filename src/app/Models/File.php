<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

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
        return $this->belongsToMany(Goal::class, 'FileGoalLinks', 'file_id', 'goal_id');
    }

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'FileNoteLinks', 'file_id', 'note_id');
    }

    // Helper methods for file operations
    public function getFileSize(): string
    {
        if ($this->file_size < 1024) {
            return $this->file_size . ' B';
        } elseif ($this->file_size < 1024 * 1024) {
            return round($this->file_size / 1024, 2) . ' KB';
        } elseif ($this->file_size < 1024 * 1024 * 1024) {
            return round($this->file_size / (1024 * 1024), 2) . ' MB';
        } else {
            return round($this->file_size / (1024 * 1024 * 1024), 2) . ' GB';
        }
    }

    public function getFileExtension(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function isImage(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        return in_array(strtolower($this->getFileExtension()), $imageExtensions);
    }

    public function isDocument(): bool
    {
        $docExtensions = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'];
        return in_array(strtolower($this->getFileExtension()), $docExtensions);
    }

    // Goal linking methods
    public function linkGoal(int $goalId): bool
    {
        if ($this->isLinkedToGoal($goalId)) {
            return false; // Already linked
        }

        return DB::table('FileGoalLinks')->insert([
            'file_id' => $this->file_id,
            'goal_id' => $goalId,
            'created_at' => now()
        ]);
    }

    public function unlinkGoal(int $goalId): bool
    {
        return DB::table('FileGoalLinks')
            ->where('file_id', $this->file_id)
            ->where('goal_id', $goalId)
            ->delete() > 0;
    }

    public function isLinkedToGoal(int $goalId): bool
    {
        return DB::table('FileGoalLinks')
            ->where('file_id', $this->file_id)
            ->where('goal_id', $goalId)
            ->exists();
    }

    // Note linking methods
    public function linkNote(int $noteId): bool
    {
        if ($this->isLinkedToNote($noteId)) {
            return false; // Already linked
        }

        return DB::table('FileNoteLinks')->insert([
            'file_id' => $this->file_id,
            'note_id' => $noteId,
            'created_at' => now()
        ]);
    }

    public function unlinkNote(int $noteId): bool
    {
        return DB::table('FileNoteLinks')
            ->where('file_id', $this->file_id)
            ->where('note_id', $noteId)
            ->delete() > 0;
    }

    public function isLinkedToNote(int $noteId): bool
    {
        return DB::table('FileNoteLinks')
            ->where('file_id', $this->file_id)
            ->where('note_id', $noteId)
            ->exists();
    }

    // Check if file has any attachments/links
    public function hasGoalLinks(): bool
    {
        return $this->goals()->exists();
    }

    public function hasNoteLinks(): bool
    {
        return $this->notes()->exists();
    }

    public function hasAnyLinks(): bool
    {
        return $this->hasGoalLinks() || $this->hasNoteLinks();
    }

    // Get link counts
    public function getGoalLinksCount(): int
    {
        return $this->goals()->count();
    }

    public function getNoteLinksCount(): int
    {
        return $this->notes()->count();
    }
}
