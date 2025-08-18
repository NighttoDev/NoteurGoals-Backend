<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Cho phép gán hàng loạt các cột này
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'read_at',
    ];

    /**
     * Định nghĩa mối quan hệ "tin nhắn này thuộc về một người gửi (sender)".
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        // Tham số 1: Model liên quan (User)
        // Tham số 2: Khóa ngoại trong bảng `messages` (sender_id)
        // Tham số 3: Khóa chính trong bảng `Users` (user_id)
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
}