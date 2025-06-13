<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'Users';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'display_name',
        'email',
        'password_hash',
        'avatar_url',
        'registration_type',
        'status',
        'reset_token',
        'last_login_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'reset_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the password for the user.
     * Cần định nghĩa phương thức này để Laravel Auth có thể làm việc với trường password_hash
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Relationships
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'user_id');
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'user_id_1')
            ->orWhere('user_id_2', $this->id);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Helper methods
    public function isPremium()
    {
        return $this->profile && $this->profile->is_premium;
    }

    public function hasActiveSubscription()
    {
        return $this->subscriptions()
            ->where('end_date', '>', now())
            ->where('payment_status', 'active')
            ->exists();
    }
}
