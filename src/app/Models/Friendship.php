<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    protected $table = 'Friendships';
    protected $primaryKey = 'friendship_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id_1',
        'user_id_2',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_1', 'user_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_2', 'user_id');
    }
}
