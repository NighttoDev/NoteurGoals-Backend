<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;
    
    protected $table = 'UserProfiles';
    protected $primaryKey = 'profile_id';

    /**
     * Các thuộc tính có thể gán hàng loạt
     */
    protected $fillable = [
        'user_id',
        'is_premium'
    ];

    /**
     * Các thuộc tính cần ép kiểu
     */
    protected $casts = [
        'is_premium' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Mối quan hệ với User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}